<?php

namespace Ebizmarts\BrippoPayments\Controller\PaymentElement;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\Service;
use Ebizmarts\BrippoPayments\Helper\ConnectedAccounts;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Ebizmarts\BrippoPayments\Helper\PaymentElement as PaymentElementHelper;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentElementStandalone;
use Error;
use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Store\Model\StoreManagerInterface;

class PlaceOrder extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $brippoApiPaymentIntents;
    protected $dataHelper;
    protected $checkoutSession;
    protected $storeManager;
    protected $customerSession;
    protected $quoteManagement;
    protected $paymentsHelper;
    protected $paymentElementHelper;
    protected $connectedAccountsHelper;

    /** @var EventManager */
    protected $eventManager;

    /** @var Stripe */
    protected $stripeHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param CartManagementInterface $quoteManagement
     * @param PaymentsHelper $paymentsHelper
     * @param PaymentElementHelper $paymentElementHelper
     * @param ConnectedAccounts $connectedAccountsHelper
     * @param EventManager $eventManager
     * @param Stripe $stripeHelper
     */
    public function __construct(
        Context                 $context,
        JsonFactory             $jsonFactory,
        Logger                  $logger,
        BrippoPaymentIntentsApi $brippoApiPaymentIntents,
        DataHelper              $dataHelper,
        CheckoutSession         $checkoutSession,
        StoreManagerInterface   $storeManager,
        CustomerSession         $customerSession,
        CartManagementInterface $quoteManagement,
        PaymentsHelper          $paymentsHelper,
        PaymentElementHelper    $paymentElementHelper,
        ConnectedAccounts       $connectedAccountsHelper,
        EventManager            $eventManager,
        Stripe                  $stripeHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->quoteManagement = $quoteManagement;
        $this->paymentsHelper = $paymentsHelper;
        $this->paymentElementHelper = $paymentElementHelper;
        $this->connectedAccountsHelper = $connectedAccountsHelper;
        $this->eventManager = $eventManager;
        $this->stripeHelper = $stripeHelper;
    }

    public function execute()
    {
        try {
            $this->logger->log('Trying to place order from Payment Element form.');
            $this->paymentElementHelper->setParamsFromRequestBody($this->getRequest());
            $scopeId = $this->storeManager->getStore()->getId();
            $isRecovery = $this->getRequest()->getParam('isRecovery') === 'true';
            $billingAddress = $this->getRequest()->getParam('billingAddress');
            $shippingAddress = $this->getRequest()->getParam('shippingAddress');
            $paymentMethod = $this->getRequest()->getParam('paymentMethod');
            $checkoutEmail = $this->getRequest()->getParam('email');
            $placementId = $this->getRequest()->getParam('placementId');

            $walletProvider = $this->stripeHelper->getWalletNameFromPaymentMethod($paymentMethod);
            $card = $this->stripeHelper->getCardFromPaymentMethod($paymentMethod);
            $quote = $this->checkoutSession->getQuote();
            $currency = $this->paymentElementHelper->getPaymentIntentCurrencyFromQuote($quote, $scopeId);

            /*
             * Fill possibly missing quote data
             */
            if (!$this->paymentElementHelper->isAddressValidForOrderSubmit($quote->getBillingAddress())) {
                $this->paymentElementHelper->setBillingAddressFromFrontendData($quote, $billingAddress);
            }
            if (!$quote->isVirtual()
                && !$this->paymentElementHelper->isAddressValidForOrderSubmit($quote->getShippingAddress())
                && !empty($shippingAddress)) {
                $this->paymentElementHelper->setShippingAddressFromFrontendData($quote, $shippingAddress);
            }
            $customerEmail = $quote->getCustomerEmail();
            if (!$this->customerSession->isLoggedIn()) {
                $quote->setCustomerIsGuest(true);
                if (empty($customerEmail)) {
                    if (filter_var($quote->getBillingAddress()->getEmail(), FILTER_VALIDATE_EMAIL)) {
                        $quote->setCustomerEmail($quote->getBillingAddress()->getEmail());
                        $customerEmail = $quote->getBillingAddress()->getEmail();
                    } elseif (!empty($checkoutEmail)) {
                        $quote->setCustomerEmail($checkoutEmail);
                        $customerEmail = $checkoutEmail;
                    } else {
                        $quote->setCustomerEmail('guest@example.com');
                    }
                }
                if (empty($quote->getCustomerFirstname())
                    && !empty($billingAddress)
                    && isset($billingAddress['firstname'])) {
                    $quote->setCustomerFirstname($billingAddress['firstname']);
                    $quote->setCustomerLastname($billingAddress['lastname']);
                }
            }

            $liveMode = $this->dataHelper->isLiveMode($scopeId);
            $quote = $this->paymentElementHelper->fillMissingDataForPlaceOrder(
                $quote,
                $placementId === PaymentElementHelper::PLACEMENT_ID_CHECKOUT_STANDALONE
                    ? PaymentElementStandalone::METHOD_CODE
                    : PaymentElement::METHOD_CODE
            );
            $accountId = $this->dataHelper->getAccountId(
                $scopeId,
                $liveMode
            );

            $this->paymentElementHelper->addValidationHashToQuote($quote);
            $this->eventManager->dispatch('checkout_submit_before', ['quote' => $quote]);

            try {
                $order = $this->quoteManagement->submit($quote);
                $this->logger->logOrderEvent(
                    $order,
                    sprintf(
                        'Order #%s placed with %s.',
                        $order->getIncrementId(),
                        PaymentElement::METHOD_CODE
                    )
                );
                $order->setStatus(BrippoOrder::STATUS_PENDING)->save();
            } catch (Error $e) {
                $this->logger->log($e->getMessage());
                $this->logger->log($e->getTraceAsString());
                throw new LocalizedException(
                    __('A server error stopped your order from being placed. Please try to place your order again.')
                );
            }

            try {
                $this->checkoutSession->clearHelperData();
                $this->checkoutSession
                    ->setLastQuoteId($quote->getId())
                    ->setLastSuccessQuoteId($quote->getId())
                    ->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());

                $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);
            } catch (Exception $ex) {
                $this->eventManager->dispatch('brippo_checkout_submit_all_after_failed',
                    [
                        'error' => $ex,
                        'order' => $order,
                        'quote' => $quote
                    ]
                );
                $this->logger->logOrderEvent(
                    $order,
                    $ex->getMessage() . ' ' . $ex->getTraceAsString()
                );
            }

            try {
                $this->logger->logOrderEvent(
                    $order,
                    'Creating payment intent...'
                );

                $paymentIntentData = $this->brippoApiPaymentIntents->create(
                    $this->paymentElementHelper->getPlaceOrderAmount($order, $scopeId),
                    $currency,
                    $this->paymentElementHelper->getCaptureMethod($scopeId, $isRecovery),
                    $accountId,
                    $this->connectedAccountsHelper->getCountry($accountId, $scopeId),
                    $this->paymentsHelper->getMetadataForPaymentIntent(
                        $accountId,
                        $customerEmail,
                        PaymentElement::METHOD_CODE,
                        $order->getIncrementId(),
                        $walletProvider,
                        $quote->getEntityId(),
                        $isRecovery ? PaymentElement::STORE_LOCATION_RETRY_MODAL : 'checkout'
                    ),
                    $this->paymentsHelper->getPaymentIntentDescription($order->getIncrementId()),
                    $liveMode,
                    $card,
                    $order->getIncrementId(),
                    isset($paymentMethod['type']) ? $paymentMethod['type'] : "",
                    $this->paymentElementHelper->getThreeDSecure($scopeId, $order),
                    $this->dataHelper->getStoreConfig(
                        DataHelper::XML_PATH_STATEMENT_DESCRIPTOR_SUFFIX,
                        $scopeId
                    )
                );
                $this->logger->logOrderEvent(
                    $order,
                    'Created payment intent ' . $paymentIntentData[Stripe::PARAM_ID] . '.'
                );
            } catch (Exception $ex) {
                $this->paymentsHelper->cancelOrder(
                    $order,
                    'Order cancelled as payment failed with error: ' . $ex->getMessage(),
                    $this->paymentElementHelper->getCancelStatusFromBrippoApiError($ex)
                );
                $this->paymentsHelper->restoreQuote($order);
                throw $ex;
            }

            try {
                $this->paymentsHelper->saveCardAdditionalData(
                    $order->getPayment(),
                    $card,
                    $walletProvider
                );
                $this->paymentsHelper->savePaymentAdditionalData(
                    $order->getPayment(),
                    $liveMode,
                    $paymentIntentData[Service::PARAM_PI_ID],
                    'checkout',
                    $paymentIntentData[Service::PARAM_PI_STATUS],
                    $currency
                );
            } catch (Exception $ex) {
                $this->logger->logOrderEvent(
                    $order,
                    $ex->getMessage() . ' ' . $ex->getTraceAsString()
                );
            }

            $response = [
                'valid' => 1,
                'client_secret' => $paymentIntentData[Service::PARAM_PI_CLIENT_SECRET],
                'payment_intent_id' => $paymentIntentData[Service::PARAM_PI_ID],
                'order_id' => $order->getEntityId(),
                'order_increment_id' => $order->getIncrementId()
            ];
        } catch (Exception $ex) {
            if (!empty($order)) {
                $this->logger->logOrderEvent(
                    $order,
                    $ex->getMessage()
                );
            } else {
                $this->logger->log($ex->getMessage());
            }

            $response = [
                'valid' => 0,
                'message' => $ex->getMessage()
            ];
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
