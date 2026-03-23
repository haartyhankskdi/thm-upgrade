<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\Service;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\Config\Source\CurrencyMode;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory as PaymentCollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;

class Payments extends AbstractHelper
{
    const TRANSFER_PAYMENT_PLACEHOLDER_ORDER_INCREM = "ORDER_INCREM";
    const TRANSFER_PAYMENT_PLACEHOLDER_CUSTOMER_EMAIL = "CUSTOMER_EMAIL";

    protected $logger;
    protected $transaction;
    protected $invoiceSender;
    protected $invoiceService;
    protected $dataHelper;
    protected $brippoApiPaymentIntents;
    protected $customersHelper;
    protected $stripeHelper;
    protected $orderSender;
    protected $orderManagement;
    protected $checkoutSession;

    /** @var RequestInterface */
    protected $request;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var PaymentCollectionFactory */
    protected $paymentCollectionFactory;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param Transaction $transaction
     * @param InvoiceSender $invoiceSender
     * @param InvoiceService $invoiceService
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param Data $dataHelper
     * @param Customers $customersHelper
     * @param Stripe $stripeHelper
     * @param OrderSender $orderSender
     * @param OrderManagementInterface $orderManagement
     * @param CheckoutSession $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentCollectionFactory $paymentCollectionFactory
     */
    public function __construct(
        Context                  $context,
        Logger                   $logger,
        Transaction              $transaction,
        InvoiceSender            $invoiceSender,
        InvoiceService           $invoiceService,
        BrippoPaymentIntentsApi  $brippoApiPaymentIntents,
        DataHelper               $dataHelper,
        Customers                $customersHelper,
        Stripe                   $stripeHelper,
        OrderSender              $orderSender,
        OrderManagementInterface $orderManagement,
        CheckoutSession          $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        PaymentCollectionFactory $paymentCollectionFactory
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceService = $invoiceService;
        $this->dataHelper = $dataHelper;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->customersHelper = $customersHelper;
        $this->stripeHelper = $stripeHelper;
        $this->orderSender = $orderSender;
        $this->orderManagement = $orderManagement;
        $this->checkoutSession = $checkoutSession;
        $this->request = $context->getRequest();
        $this->orderRepository = $orderRepository;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
    }

    /**
     * @param OrderInterface $order
     * @param string $paymentIntentId
     * @param bool $sendConfirmationEmail
     * @return InvoiceInterface|Order\Invoice
     * @throws LocalizedException
     */
    public function invoiceOrder(
        OrderInterface $order,
        string $paymentIntentId,
        bool $sendConfirmationEmail = true
    ) {
        $this->logger->logOrderEvent(
            $order,
            'Trying to invoice order...');

        $payment = $order->getPayment();
        $payment->setTransactionId($paymentIntentId);
        $payment->setBaseAmountAuthorized($payment->getBaseAmountOrdered());
        $payment->setParentTransactionId($payment->getTransactionId());
        $payment->save();

        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(Order\Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $invoice->save();

        $transaction = $this->transaction
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transaction->save();
        $this->invoiceSender->send($invoice);

        $order->addCommentToStatusHistory(
            __(
                'Notified customer about invoice creation #%1.',
                $invoice->getIncrementId()
            )
        )->setIsCustomerNotified(true)->save();

        $this->logger->logOrderEvent(
            $order,
            'Successfully created invoice #' . $invoice->getIncrementId() . '.'
        );

        if ($sendConfirmationEmail) {
            $this->orderSender->send($order);
        }

        return $invoice;
    }

    /**
     * @param OrderInterface $order
     * @param string $cancelMessage
     * @param string $orderStatus
     * @return void
     * @throws Exception
     */
    public function cancelOrder(
        OrderInterface $order,
        string $cancelMessage,
        string $orderStatus = 'canceled'
    ): void
    {
        $this->logger->logOrderEvent(
            $order,
            'Trying to cancel order and set status ' . $orderStatus . '...');

        if ($this->orderManagement->cancel($order->getEntityId())) {
            $order->addCommentToStatusHistory(__($cancelMessage));

            $order->setState(Order::STATE_CANCELED)
                ->setStatus($orderStatus)
                ->save();

            $this->logger->logOrderEvent(
                $order,
                'Canceled: ' . $cancelMessage . '.'
            );
        } else {
            $this->logger->logOrderEvent(
                $order,
                'Unable to cancel.'
            );
        }
    }

    /**
     * @param $orderIncrementId
     * @return string
     */
    public function getPaymentIntentDescription($orderIncrementId = null)
    {
        return "From " . $this->dataHelper->getStoreDomain()
            . (empty($orderIncrementId) ? "" : " Order #" . $orderIncrementId);
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param bool $liveMode
     * @param $paymentIntentId
     * @param $source
     * @param $status
     * @param $currency
     * @return void
     * @throws Exception
     */
    public function savePaymentAdditionalData(
        OrderPaymentInterface $payment,
        bool $liveMode,
        $paymentIntentId,
        $source,
        $status,
        $currency
    ) {
        if (!empty($paymentIntentId)) {
            $payment->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID,
                $paymentIntentId
            );
        }

        $payment->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_LIVEMODE,
            $liveMode
        );

        if (!empty($source)) {
            $payment->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_FRONTEND_SOURCE,
                $source
            );
        }

        if (!empty($status)) {
            $payment->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_STATUS,
                $status
            );
        }

        if (!empty($currency)) {
            $payment->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_CURRENCY,
                $status
            );
        }

        $payment->save();
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param $card
     * @param $wallet
     * @return void
     * @throws Exception
     */
    public function saveCardAdditionalData(
        OrderPaymentInterface $payment,
        $card,
        $wallet
    ): void
    {
        if (!empty($card)) {
            if (isset($card['brand'])) {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_CARD_BRAND,
                    $card['brand']
                );
            }

            if (isset($card['last4'])) {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_CARD_LAST4,
                    $card['last4']
                );
            }

            if (isset($card['country'])) {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_CARD_COUNTRY,
                    $card['country']
                );
            }

            if (isset($card['brand_product'])) {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_CARD_BRAND_PRODUCT,
                    $card['brand_product']
                );
            }

            if (isset($card['exp_month'])) {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_CARD_EXP_MONTH,
                    $card['exp_month']
                );
            }

            if (isset($card['exp_year'])) {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_CARD_EXP_YEAR,
                    $card['exp_year']
                );
            }

            if (isset($card['funding'])) {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_FUNDING,
                    $card['funding']
                );
            }
        }

        if (!empty($wallet)) {
            $payment->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_WALLET,
                $wallet
            );
        }

        $payment->save();
    }

    /**
     * @param string $accountId
     * @param $customerEmail
     * @param string $paymentMethodCode
     * @param $orderId
     * @param $walletProvider
     * @param $quoteId
     * @param $sourceLocation
     * @param OrderAddressInterface|null $billingAddress
     * @return array
     */
    public function getMetadataForPaymentIntent(
        string $accountId,
        $customerEmail,
        string $paymentMethodCode,
        $orderId,
        $walletProvider,
        $quoteId,
        $sourceLocation,
        ?OrderAddressInterface $billingAddress = null
    ): array {
        $metadata = [
            Stripe::METADATA_KEY_ACCOUNT_ID => $accountId,
            Stripe::METADATA_KEY_EXTENSION_SIGNATURE => $this->dataHelper->getExtensionsVersionString(),
            Stripe::METADATA_KEY_PAYMENT_METHOD_CODE => $paymentMethodCode,
            Stripe::METADATA_KEY_ORDER_ID => $orderId,
            Stripe::METADATA_KEY_QUOTE_ID => $quoteId,
            Stripe::METADATA_KEY_MAGENTO_EDITION => $this->dataHelper->getMagentoEditionString(),
            Stripe::METADATA_KEY_MAGENTO_VERSION => $this->dataHelper->getMagentoVersionString(),
            Stripe::METADATA_KEY_STORE_URL => $this->dataHelper->getStoreDomain(),
            Stripe::METADATA_KEY_IP_ADDRESS => $this->dataHelper->getClientIpAddress(),
            Stripe::METADATA_KEY_USER_AGENT => $this->request->getServer('HTTP_USER_AGENT'),
            Stripe::METADATA_KEY_BRIPPO_UNIQUE_ID => $this->checkoutSession->getBrippoOrderUniqId() ?? 'unknown'
        ];

        if (!empty($walletProvider)) {
            $metadata[Stripe::METADATA_KEY_WALLET_PROVIDER] = $walletProvider;
        }

        if (!empty($sourceLocation)) {
            $metadata[Stripe::METADATA_KEY_SOURCE_LOCATION] = $sourceLocation;
        }

        if (!empty($customerEmail)) {
            $metadata[Stripe::METADATA_KEY_CUSTOMER_EMAIL] = $customerEmail;
        }

        if (!empty($billingAddress)) {
            $metadata[Stripe::METADATA_KEY_BILLING_ADDRESS] = $this->billingAddressToString($billingAddress);

            if (!empty($billingAddress->getFirstname())) {
                $metadata[Stripe::METADATA_KEY_BILLING_NAME] = $billingAddress->getFirstname()
                    . (!empty($billingAddress->getMiddlename())
                        ? ' ' . $billingAddress->getMiddlename()
                        : '')
                    . (!empty($billingAddress->getLastname())
                        ? ' ' . $billingAddress->getLastname()
                        : '');
            }
            if (!empty($billingAddress->getTelephone())) {
                $metadata[Stripe::METADATA_KEY_BILLING_PHONE] = $billingAddress->getTelephone();
            }
            if (!empty($billingAddress->getEmail())) {
                $metadata[Stripe::METADATA_KEY_BILLING_EMAIL] = $billingAddress->getEmail();
            }
        }

        return $metadata;
    }

    private function billingAddressToString(?OrderAddressInterface $billingAddress): string {
        if ($billingAddress === null) {
            return '';
        }

        try {
            $lines = [];
            $streetLines = $billingAddress->getStreet() ?: [];
            foreach ($streetLines as $line) {
                if (trim((string)$line) !== '') {
                    $lines[] = trim($line);
                }
            }
            $city     = trim($billingAddress->getCity());
            $region   = trim((string)$billingAddress->getRegion());
            $postcode = trim($billingAddress->getPostcode());
            $location = array_filter([$city, $region, $postcode]);
            if (!empty($location)) {
                $lines[] = implode(', ', $location);
            }
            $countryCode = trim($billingAddress->getCountryId());
            if ($countryCode !== '') {
                $lines[] = $countryCode;
            }

            return implode("\n", $lines);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param array $paymentIntentData
     * @return void
     * @throws Exception
     */
    public function savePaymentFraudDetails(OrderPaymentInterface $payment, array $paymentIntentData): void
    {
        if (!isset($paymentIntentData[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_OUTCOME])) {
            return;
        }

        $chargeData = $paymentIntentData[Stripe::PARAM_LATEST_CHARGE];
        $chargeOutcome = $chargeData[Stripe::PARAM_OUTCOME];
        $radarRisk = $chargeOutcome[Stripe::PARAM_RISK_LEVEL] ?? null;
        $streetCheck = $this->stripeHelper->getStreetCheck($chargeData);
        $zipCheck = $this->stripeHelper->getZipCheck($chargeData);
        $cvcCheck = $this->stripeHelper->getCVCCheck($chargeData);

        $payment->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_RADAR_RISK,
            $radarRisk ?? ""
        )->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_STREET_CHECK,
            $streetCheck ?? ""
        )->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_ZIP_CHECK,
            $zipCheck ?? ""
        )->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_CVC_CHECK,
            $cvcCheck ?? ""
        )->save();
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param array $paymentIntentData
     * @return void
     * @throws Exception
     */
    public function savePayment3DSDetails(OrderPaymentInterface $payment, array $paymentIntentData)
    {
        if (!isset($paymentIntentData[Stripe::PARAM_LATEST_CHARGE])) {
            if (isset($paymentIntentData[Stripe::PARAM_LAST_PAYMENT_ERROR][Stripe::PARAM_CODE])
                && $paymentIntentData[Stripe::PARAM_LAST_PAYMENT_ERROR]
                [Stripe::PARAM_CODE] === 'payment_intent_authentication_failure') {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_3D_SECURE,
                    PaymentMethod::ADDITIONAL_DATA_FAILED
                )->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_LAST_PAYMENT_ERROR,
                    $paymentIntentData[Stripe::PARAM_LAST_PAYMENT_ERROR]
                )->save();
            }

            return;
        }
        $latestCharge = $paymentIntentData[Stripe::PARAM_LATEST_CHARGE];
        if (!isset(
            $latestCharge[Stripe::PARAM_PAYMENT_METHOD_DETAILS][Stripe::PARAM_CARD][Stripe::PARAM_THREE_D_SECURE],
            $latestCharge[Stripe::PARAM_STATUS],
            $latestCharge[Stripe::PARAM_OUTCOME][Stripe::PARAM_NETWORK_STATUS]
        )) {
            $payment->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_3D_SECURE,
                PaymentMethod::ADDITIONAL_DATA_NOT_PRESENT
            )->save();
        }

        $threeDSecure = $latestCharge[Stripe::PARAM_PAYMENT_METHOD_DETAILS][Stripe::PARAM_CARD]
            [Stripe::PARAM_THREE_D_SECURE];
        $threeDSecureResult = $threeDSecure[Stripe::PARAM_RESULT] ?? null;
        $chargeStatus = $latestCharge[Stripe::PARAM_STATUS];
        $networkStatus = $latestCharge[Stripe::PARAM_OUTCOME][Stripe::PARAM_NETWORK_STATUS];

        if ($threeDSecure) {
            if ($threeDSecureResult === 'authenticated' && $chargeStatus === 'succeeded') {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_3D_SECURE,
                    PaymentMethod::ADDITIONAL_DATA_PASSED
                )->save();
            } elseif ($networkStatus === 'declined_by_network' || $chargeStatus === 'failed') {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_3D_SECURE,
                    PaymentMethod::ADDITIONAL_DATA_REJECTED
                )->save();
            } elseif ($threeDSecureResult === 'attempt_acknowledged') {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_3D_SECURE,
                    PaymentMethod::ADDITIONAL_DATA_ATTEMPTED
                )->save();
            }
        }
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return void
     * @throws Exception
     */
    public function resetPaymentDetails(OrderPaymentInterface $payment): void
    {
        $payment->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_RADAR_RISK,
            null
        )->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_STREET_CHECK,
            null
        )->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_ZIP_CHECK,
            null
        )->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_CVC_CHECK,
            null
        )->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_3D_SECURE,
            null
        )->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_LAST_PAYMENT_ERROR,
            null
        )->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_FAILED,
            null
        )->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_FRAUD_NOT_AVAILABLE,
            null
        )->save();
    }

    /**
     * @param $response
     * @return string|null
     */
    public function getDestinationChargeIdFromResponse($response)
    {
        if ($response && isset($response[Service::PARAM_DESTINATION_CHARGE])
            && !empty($response[Service::PARAM_DESTINATION_CHARGE])) {
            return $response[Service::PARAM_DESTINATION_CHARGE]['id'];
        }
        return null;
    }

    /**
     * @param OrderInterface $order
     * @param int $scopeId
     * @return array|string|string[]
     */
    private function getTransferPaymentDescription(OrderInterface $order, int $scopeId)
    {
        $descriptionConfig = "Order #ORDER_INCREM from CUSTOMER_EMAIL";

        $descriptionConfig = str_replace(
            self::TRANSFER_PAYMENT_PLACEHOLDER_ORDER_INCREM,
            $order->getIncrementId(),
            $descriptionConfig
        );
        $descriptionConfig = str_replace(
            self::TRANSFER_PAYMENT_PLACEHOLDER_CUSTOMER_EMAIL,
            $order->getCustomerEmail() ?? '',
            $descriptionConfig
        );
        return $descriptionConfig;
    }

    /**
     * @param string $paymentIntentId
     * @param int $scopeId
     * @param OrderInterface $order
     * @param $metadata
     * @return array
     * @throws LocalizedException
     */
    public function linkTransferCharge(string $paymentIntentId, int $scopeId, OrderInterface $order, $metadata)
    {
        $livemode = $this->dataHelper->isLiveMode($scopeId);
        $response = $this->brippoApiPaymentIntents->updateTransferCharge(
            $paymentIntentId,
            $this->getTransferPaymentDescription($order, $scopeId),
            $this->getPaymentIntentDescription($order->getIncrementId()),
            $metadata,
            $this->dataHelper->getAccountId(
                $scopeId,
                $livemode
            ),
            $livemode
        );

        $payment = $order->getPayment();

        if (!$payment) {
            throw new LocalizedException(__('Order payment not found.'));
        }

        $destinationChargeId = $this->getDestinationChargeIdFromResponse($response);
        if ($destinationChargeId != null) {
            $payment->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_TRANSFER_CHARGE_ID,
                $destinationChargeId
            )->save();

            $this->logger->log('Order #' . $order->getIncrementId()
                . " successfully linked to Charge " . $destinationChargeId);
        } else {
            $this->logger->log('Unable to link transfer charge. There is no destination charge.');
        }

        return $response;
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function restoreQuote(OrderInterface $order)
    {
        $this->logger->logOrderEvent(
            $order,
            'Trying to restore quote...'
        );
        $this->checkoutSession
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId());
        if ($this->checkoutSession->restoreQuote()) {
            $this->logger->logOrderEvent(
                $order,
                'Quote restored.'
            );
        } else {
            $this->logger->logOrderEvent(
                $order,
                'Unable to restore quote.'
            );
        }
    }

    /**
     * @param OrderInterface $order
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPaymentCurrency(OrderInterface $order):string
    {
        $currency = $order->getOrderCurrencyCode();
        if (!empty($currency) &&
            !empty($order->getBaseCurrencyCode()) &&
            $order->getBaseCurrencyCode() !== $currency &&
            $this->dataHelper->getStoreConfig(
                DataHelper::XML_PATH_CURRENCY_MODE,
                $order->getStoreId()
            ) === CurrencyMode::MODE_BASE_CURRENCY) {
            $currency = $order->getBaseCurrencyCode();
        }

        if (empty($currency)) {
            $currency = $order->getStore()->getCurrentCurrency()->getCode();
        }

        if (empty($currency)) {
            throw new LocalizedException(__('Store currency is not configured'));
        }

        return $currency;
    }

    /**
     * @param OrderInterface $order
     * @param string $newPaymentMethod
     * @param bool $isRecoverCheckout
     * @return void
     * @throws Exception
     */
    public function recoverOrder(OrderInterface $order, string $newPaymentMethod, bool $isRecoverCheckout = true): void
    {
        $this->logger->logOrderEvent(
            $order,
            'Trying to recover order...');

        $order->setTotalCanceled(0);
        $order->setSubtotalCanceled(0);
        $order->setBaseTotalCanceled(0);
        $order->setBaseSubtotalCanceled(0);

        foreach ($order->getAllItems() as $item) {
            $item->setQtyCanceled(0);
            $item->save();
        }

        if ($order->getPayment()->getMethod() !== $newPaymentMethod) {
            $order->getPayment()->setMethod($newPaymentMethod);
        }

        if ($isRecoverCheckout) {
            $order->addCommentToStatusHistory(__('Recovered through Brippo Recover Checkout'));
        } else {
            $order->addCommentToStatusHistory(__('Order recovered as it was cancelled'));
        }
        $this->orderRepository->save($order);

        $this->logger->logOrderEvent(
            $order,
            'Order successfully recovered.');
    }

    /**
     * @param string $paymentIntentId
     * @return OrderInterface|null
     */
    public function getOrderByPaymentIntentId(string $paymentIntentId)
    {
        $payments = $this->paymentCollectionFactory->create();
        $payments->addFieldToFilter(
            'additional_information',
            ['like' => '%"' . PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID . '":"' . $paymentIntentId . '"%']
        );

        if ($payments->getSize() === 1) {
            $orderId = $payments->getFirstItem()->getParentId();
            return $this->orderRepository->get($orderId);
        }

        return null;
    }

    /**
     * @param $latestCharge
     * @return bool
     */
    public function isFailedChargeAllowedForRecovery($latestCharge):bool {
        $this->logger->log(print_r($latestCharge, true));
        return true;
    }

    public function processUncapturedPaymentOrder(OrderInterface $order): void {
        $order->setState(Order::STATE_PENDING_PAYMENT)
            ->setStatus(BrippoOrder::STATUS_AUTHORIZED)
            ->save();

        if (!$order->getEmailSent()) {
            $this->orderSender->send($order);
        }
    }
}
