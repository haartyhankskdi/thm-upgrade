<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\Receipts;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\Service;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\Config\Source\CaptureMethod;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod as PaymentMethodsHelper;
use Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class TerminalBackend extends PaymentMethodsHelper
{
    protected $brippoApiPaymentIntents;
    protected $brippoApiReceipts;
    protected $stripeHelper;
    protected $connectedAccountsHelper;
    protected $paymentsHelper;
    protected $orderSender;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param Json $json
     * @param EncryptorInterface $encryptor
     * @param PaymentIntents $brippoApiPaymentIntents
     * @param Receipts $brippoApiReceipts
     * @param ConnectedAccounts $connectedAccountsHelper
     * @param Payments $paymentsHelper
     * @param OrderSender $orderSender
     * @param Stripe $stripeHelper
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        Json $json,
        EncryptorInterface $encryptor,
        PaymentIntents $brippoApiPaymentIntents,
        Receipts $brippoApiReceipts,
        ConnectedAccounts $connectedAccountsHelper,
        Payments $paymentsHelper,
        OrderSender $orderSender,
        Stripe $stripeHelper
    ) {
        parent::__construct($context, $dataHelper, $json, $encryptor);
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->brippoApiReceipts = $brippoApiReceipts;
        $this->connectedAccountsHelper = $connectedAccountsHelper;
        $this->paymentsHelper = $paymentsHelper;
        $this->orderSender = $orderSender;
        $this->stripeHelper = $stripeHelper;
    }

    /**
     * @param Order $order
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function onPlaceOrder(Order $order): void
    {
        $this->dataHelper->logger->logOrderEvent(
            $order,
            'Order placed successfully, creating payment intent...'
        );

        $currency = $order->getOrderCurrencyCode();
        $scopeId = $order->getStore()->getId();
        $liveMode = $this->dataHelper->isLiveMode($scopeId);
        $customerEmail = $order->getCustomerEmail();
        $quoteId = $order->getQuoteId();

        $accountId = $this->dataHelper->getAccountId(
            $scopeId,
            $liveMode
        );

        $paymentIntent = $this->brippoApiPaymentIntents->create(
            $this->getPlaceOrderAmount($order, $scopeId),
            $currency,
            Stripe::CAPTURE_METHOD_MANUAL, //We need to recalculate fee after payment method assigned
            $accountId,
            $this->connectedAccountsHelper->getCountry($accountId, $scopeId),
            $this->paymentsHelper->getMetadataForPaymentIntent(
                $accountId,
                $customerEmail,
                \Ebizmarts\BrippoPayments\Model\TerminalBackend::METHOD_CODE,
                $order->getIncrementId(),
                null,
                $quoteId,
                null,
                $order->getBillingAddress()
            ),
            $this->paymentsHelper->getPaymentIntentDescription($order->getIncrementId()),
            $liveMode,
            null,
            $order->getIncrementId(),
            "",
            "",
            $this->dataHelper->getStoreConfig(
                DataHelper::XML_PATH_STATEMENT_DESCRIPTOR_SUFFIX,
                $scopeId
            ),
            false,
            [
                Stripe::PARAM_CARD,
                Stripe::PARAM_CARD_PRESENT
            ],
            true
        );

        $this->dataHelper->logger->logOrderEvent(
            $order,
            'Successfully created payment intent ' . $paymentIntent[Service::PARAM_PI_ID]
        );

        $order->setState(Order::STATE_PENDING_PAYMENT)
            ->setStatus(BrippoOrder::STATUS_PENDING);
        $order->save();

        $this->dataHelper->logger->log('Order #' . $order->getIncrementId() .
            ' placed successfully with Payment Intent ' .
            $paymentIntent[Service::PARAM_PI_ID] . '. Ready to send to terminal for processing.');

        $order->getPayment()->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID,
            $paymentIntent[Service::PARAM_PI_ID]
        )->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_LIVEMODE,
            $liveMode
        )->save();
    }

    /**
     * @param OrderInterface $order
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    public function onActionCompleted(OrderInterface $order): void
    {
        $this->dataHelper->logger->logOrderEvent(
            $order,
            'Processing Brippo Terminal Backend onActionCompleted...'
        );

        $payment = $order->getPayment();
        $paymentIntentId = $payment->getAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID
        );
        if (empty($paymentIntentId)) {
            $this->dataHelper->logger->logOrderEvent(
                $order,
                'Payment Intent ID not found.'
            );
            return;
        }

        $scopeId = $order->getStoreId();
        $liveMode = $payment->getAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_LIVEMODE
        );
        if (empty($liveMode)) {
            $liveMode = $this->dataHelper->isLiveMode($scopeId);
        }

        if (!$order->isCanceled() && !$order->hasInvoices()) {
            $paymentIntent = $this->brippoApiPaymentIntents->get($paymentIntentId, $liveMode);

            $receipt = $this->getReceipt(
                $liveMode,
                $this->dataHelper->getAccountId(
                    $scopeId,
                    $liveMode
                ),
                $order,
                $paymentIntent
            );
            if (!empty($receipt) && isset($receipt['receipt_number'])) {
                $payment->setAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_RECEIPT_NUMBER,
                    $this->brippoApiReceipts->normalizeReceiptNumber($receipt['receipt_number'])
                );
            }

            $currency = $paymentIntent[Stripe::PARAM_CURRENCY];
            $amount = $paymentIntent[Stripe::PARAM_AMOUNT] / 100;
            if ($paymentIntent[Stripe::PARAM_STATUS] == Stripe::PAYMENT_INTENT_STATUS_REQUIRES_CAPTURE) {
                $captureMethod = $this->dataHelper->getStoreConfig(
                    \Ebizmarts\BrippoPayments\Model\TerminalBackend::XML_PATH_CAPTURE_METHOD,
                    $scopeId
                );

                $source = 'backend';
                if ($captureMethod == CaptureMethod::AUTOMATIC_CAPTURE) {
                    $this->captureAndInvoice($order, $paymentIntentId, $liveMode, $source, $amount, $currency);
                } else {
                    $this->processManualCapturePayment($order, $paymentIntent, $liveMode, $source);
                }
            } else {
                $this->dataHelper->logger->logOrderEvent(
                    $order,
                    'Invalid payment intent status: ' . $paymentIntent['status']
                );
            }
        } else {
            if ($order->isCanceled()) {
                $this->dataHelper->logger->logOrderEvent(
                    $order,
                    'Won\'t action as order is canceled.'
                );
            } elseif ($order->hasInvoices()) {
                $this->dataHelper->logger->logOrderEvent(
                    $order,
                    'Won\'t action as order was already invoiced.'
                );
            }
        }
    }

    /**
     * @param $order
     * @param $paymentIntent
     * @param $liveMode
     * @param $source
     * @return void
     * @throws Exception
     */
    private function processManualCapturePayment($order, $paymentIntent, $liveMode, $source): void
    {
        if (isset($paymentIntent[Stripe::PARAM_LATEST_CHARGE])) {
            $cardData = $this->stripeHelper->getCardData($paymentIntent[Stripe::PARAM_LATEST_CHARGE]);
            $this->paymentsHelper->saveCardAdditionalData(
                $order->getPayment(),
                $cardData,
                null
            );
            $this->paymentsHelper->savePaymentAdditionalData(
                $order->getPayment(),
                $liveMode,
                $paymentIntent[Stripe::PARAM_ID],
                $source,
                $paymentIntent[Stripe::PARAM_STATUS],
                $paymentIntent[Stripe::PARAM_CURRENCY]
            );
        }

        $order->setState(Order::STATE_PENDING_PAYMENT)
            ->setStatus(BrippoOrder::STATUS_AUTHORIZED)
            ->save();
        $this->orderSender->send($order);
    }

    /**
     * @param $order
     * @param $paymentIntentId
     * @param $liveMode
     * @param $source
     * @param $amountToCapture
     * @param $currency
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    private function captureAndInvoice($order, $paymentIntentId, $liveMode, $source, $amountToCapture, $currency): void
    {
        $this->dataHelper->logger->logOrderEvent(
            $order,
            'Trying to capture online...'
        );

        if ($order->canInvoice()) {
            $paymentIntent = $this->brippoApiPaymentIntents->capture(
                $paymentIntentId,
                $liveMode,
                $amountToCapture,
                $currency
            );

            if ($paymentIntent['status'] != Stripe::PAYMENT_INTENT_STATUS_SUCCEEDED) {
                throw new LocalizedException(
                    __('Capture failed. Payment Intent status is ' . $paymentIntent['status'] . '.')
                );
            }

            $this->dataHelper->logger->logOrderEvent(
                $order,
                'Order successfully captured online.'
            );

            if (isset($paymentIntent[Stripe::PARAM_LATEST_CHARGE])) {
                $cardData = $this->stripeHelper->getCardData($paymentIntent[Stripe::PARAM_LATEST_CHARGE]);
                $this->paymentsHelper->saveCardAdditionalData(
                    $order->getPayment(),
                    $cardData,
                    null
                );
                $this->paymentsHelper->savePaymentAdditionalData(
                    $order->getPayment(),
                    $liveMode,
                    $paymentIntentId,
                    $source,
                    $paymentIntent['status'],
                    $currency
                );
            }

            $this->paymentsHelper->invoiceOrder($order, $paymentIntentId);
        }
    }

    /**
     * @param bool $liveMode
     * @param string $accountId
     * @param OrderInterface $order
     * @param array $paymentIntent
     * @return array|null
     */
    private function getReceipt(bool $liveMode, string $accountId, OrderInterface $order, array $paymentIntent): ?array
    {
        try {
            $this->dataHelper->logger->logOrderEvent(
                $order,
                'Generating terminal receipt...'
            );
            $receipt = $this->brippoApiReceipts->get(
                $liveMode,
                $accountId,
                $paymentIntent[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_ID]
            );
            $this->dataHelper->logger->logOrderEvent(
                $order,
                'Generated terminal receipt #'
                    . $this->brippoApiReceipts->normalizeReceiptNumber($receipt['receipt_number'])
            );
            return $receipt;
        } catch (Exception $ex) {
            $this->dataHelper->logger->logOrderEvent(
                $order,
                $ex->getMessage()
            );
        }

        return null;
    }

    /**
     * @param $message
     * @return string
     */
    public function prettifyErrorMessage($message): string
    {
        try {
            if (strpos($message, 'Reader is currently offline') !== false) {
                return __('Reader is currently offline, please ensure the reader is powered on'
                    . ' and connected to the internet before retrying your request.')->getText();
            }
        } catch (Exception $ex) {
            $this->dataHelper->logger->log($ex->getMessage());
        }

        return $message;
    }
}
