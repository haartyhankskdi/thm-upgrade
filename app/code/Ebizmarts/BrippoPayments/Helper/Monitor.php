<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\PayByLink;
use Ebizmarts\BrippoPayments\Model\PayByLinkMoto;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Ebizmarts\BrippoPayments\Model\TerminalBackend;
use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class Monitor extends AbstractHelper
{
    protected $logger;
    protected $dataHelper;
    protected $brippoApiPaymentIntents;
    protected $paymentsHelper;
    protected $monitorPlatformService;
    protected $stripeHelper;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param Payments $paymentsHelper
     * @param PlatformService\Monitor $monitorPlatformService
     * @param Data $dataHelper
     * @param Stripe $stripeHelper
     */
    public function __construct(
        Context                 $context,
        Logger                  $logger,
        BrippoPaymentIntentsApi $brippoApiPaymentIntents,
        Payments                $paymentsHelper,
        PlatformService\Monitor $monitorPlatformService,
        DataHelper              $dataHelper,
        Stripe                  $stripeHelper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->paymentsHelper = $paymentsHelper;
        $this->monitorPlatformService = $monitorPlatformService;
        $this->dataHelper = $dataHelper;
        $this->stripeHelper = $stripeHelper;
    }

    /**
     * @throws LocalizedException
     * @throws Exception
     */
    public function checkOrder(OrderInterface $order): void
    {
        $this->logger->logOrderEvent(
            $order,
        'Monitor check...'
        );

        $payment = $order->getPayment();
        $paymentIntentId = $payment->getAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID
        );
        $paymentIntentId = is_array($paymentIntentId)? reset($paymentIntentId) : $paymentIntentId;
        $liveMode = $payment->getAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_LIVEMODE
        );
        if (empty($liveMode)) {
            $liveMode = $this->dataHelper->isLiveMode($order->getStoreId());
        }

        if (empty($paymentIntentId)
            && $order->getState() !== Order::STATE_CLOSED
            && $order->getState() !== Order::STATE_CANCELED) {
            $this->checkOrphanOrder(
                (bool)$liveMode??true,
                $order,
                'Order #' . $order->getIncrementId() . ' has no payment intent Id.'
            );
            return;
        } elseif (empty($paymentIntentId)) {
            $this->logger->logOrderEvent(
                $order,
                'Order has no payment intent Id but already canceled/closed.'
            );
            return;
        }

        try {
            $paymentIntent = $this->brippoApiPaymentIntents->get($paymentIntentId, $liveMode ?? true);
        } catch (Exception $ex) {
            $this->logger->logOrderEvent(
                $order,
                $ex->getMessage()
            );
            $this->checkOrphanOrder(
                (bool)$liveMode??true,
                $order,
                'Can not get order #' . $order->getIncrementId() . ' attached payment: ' . $ex->getMessage()
            );
            return;
        }

        $paymentIntentStatus = $paymentIntent[Stripe::PARAM_STATUS];
        if ($paymentIntentStatus === Stripe::PAYMENT_INTENT_STATUS_SUCCEEDED) {
            $this->checkSuccessfulPaymentOrder($liveMode, $order, $paymentIntentId, $paymentIntent);
        } elseif ($paymentIntentStatus === Stripe::PAYMENT_INTENT_STATUS_REQUIRES_CAPTURE) {
            $this->checkRequiresCapturePaymentOrder($liveMode, $order, $paymentIntentId);
        } else {
            $this->checkInvalidPaymentOrder($liveMode, $order, $paymentIntentId, $paymentIntent);
        }
        if (isset($paymentIntent[Stripe::PARAM_LATEST_CHARGE]) &&
            !isset($payment->getAdditionalInformation()[Stripe::METADATA_KEY_RADAR_RISK])
        ) {
            $this->paymentsHelper->savePaymentFraudDetails($order->getPayment(), $paymentIntent);
        }
    }

    /**
     * @param bool $liveMode
     * @param OrderInterface $order
     * @param string $error
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    protected function checkOrphanOrder(
        bool $liveMode,
        OrderInterface $order,
        string $error
    ): void
    {
        if ($order->getPayment()->getMethod() == PayByLink::METHOD_CODE
        || $order->getPayment()->getMethod() == PayByLinkMoto::METHOD_CODE) {
            return;
        }

        if (strpos($error, 'No such payment_intent') !== false
            || strpos($error, 'no payment intent Id') !== false
        ) {
            if ($order->canCancel()) {
                $this->paymentsHelper->cancelOrder(
                    $order,
                    $error
                );
                $error .= ' Order was canceled.';
            } else {
                $error .= ' Order can not be canceled';
            }
        }
        $this->monitorPlatformService->sendNotification(
            ScopeInterface::SCOPE_STORE,
            $order->getStoreId(),
            $liveMode,
            '',
            $order->getIncrementId(),
            $error
        );
        $this->logger->logOrderEvent(
            $order,
            $error . ' Notification was sent.'
        );
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function checkSuccessfulPaymentOrder(
        bool $liveMode,
        OrderInterface $order,
        string $paymentIntentId,
        array $paymentIntent
    ): void
    {
        if ($order->getState() === Order::STATE_PENDING_PAYMENT
            || $order->getState() === Order::STATE_NEW) {
            if ($order->canInvoice()) {
                $this->logger->logOrderEvent(
                    $order,
                    'Order has successful payment ' . $paymentIntentId .
                    ' but order is in ' . $order->getState() . ' state.'
                );
                $this->paymentsHelper->invoiceOrder(
                    $order,
                    $paymentIntent[Stripe::PARAM_ID]
                );
            } else {
                $this->logger->logOrderEvent(
                    $order,
                    'Order has successful payment ' . $paymentIntentId .
                    ' but order is in ' . $order->getState() . ' state. Unable to invoice.'
                );
            }
        } elseif ($order->isCanceled()) {
            $error = 'Payment was successful but order status is canceled.';
            $this->monitorPlatformService->sendNotification(
                ScopeInterface::SCOPE_STORE,
                $order->getStoreId(),
                $liveMode,
                $paymentIntentId,
                $order->getIncrementId(),
                $error
            );
            $this->logger->logOrderEvent(
                $order,
                $error . ' Notification was sent.'
            );
        }

        /*
         * CHECK INVALID AMOUNT
         */
        $piAmount = (float)$this->stripeHelper->convertStripeAmountToMagentoAmount(
            $paymentIntent[Stripe::PARAM_AMOUNT],
            $paymentIntent[Stripe::PARAM_CURRENCY]
        );
        $orderAmount = $order->getGrandTotal();
        $amountDiff = abs($piAmount - $orderAmount);
        if ($amountDiff > 0.01) {
            $error = 'Payment intent amount for ' . $order->getIncrementId() . " doesn't match order amount" .
                "\nMagento's amount: " . $orderAmount .
                "\nPayment intent's amount: " . $piAmount;
            $this->monitorPlatformService->sendNotification(
                ScopeInterface::SCOPE_STORE,
                $order->getStoreId(),
                $liveMode,
                $paymentIntentId,
                $order->getIncrementId(),
                $error
            );
            $this->logger->logOrderEvent(
                $order,
                $error . ' Notification was sent.'
            );
        }
    }

    /**
     * @param bool $liveMode
     * @param OrderInterface $order
     * @param string $paymentIntentId
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function checkRequiresCapturePaymentOrder(
        bool $liveMode,
        OrderInterface $order,
        string $paymentIntentId
    ): void
    {
        if ($order->getState() !== Order::STATE_PENDING_PAYMENT) {
            if ($this->dataHelper->isOrderInvoicePending($order)) {
                $message = 'Order state is ' . $order->getState() . ', invoice is pending';
                $this->logger->logOrderEvent(
                    $order,
                    $message . ' Notification was not sent.'
                );
            } else {
                if ($order->getState() === Order::STATE_NEW) {
                    $this->paymentsHelper->processUncapturedPaymentOrder($order);
                } else {
                    $error = 'Payment is awaiting capture but order state is ' . $order->getState() . '.';
                    $this->monitorPlatformService->sendNotification(
                        ScopeInterface::SCOPE_STORE,
                        $order->getStoreId(),
                        $liveMode,
                        $paymentIntentId,
                        $order->getIncrementId(),
                        $error
                    );
                    $this->logger->logOrderEvent(
                        $order,
                        $error . ' Notification was sent.'
                    );
                }
            }
        }
    }

    /**
     * @param bool $liveMode
     * @param OrderInterface $order
     * @param string $paymentIntentId
     * @param array $paymentIntent
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    protected function checkInvalidPaymentOrder(
        bool $liveMode,
        OrderInterface $order,
        string $paymentIntentId,
        array $paymentIntent
    ): void
    {
        if ($order->getState() === Order::STATE_PENDING_PAYMENT ||
            $order->getState() === Order::STATE_NEW) {
            if ($order->getPayment()->getMethod() === TerminalBackend::METHOD_CODE
                && $paymentIntent[Stripe::PARAM_STATUS] === Stripe::PAYMENT_INTENT_STATUS_REQUIRES_PAYMENT_METHOD) {
                /*
                 * NORMAL TERMINAL BACKEND BEHAVIOUR
                 */
                return;
            }

            $this->logger->logOrderEvent(
                $order,
                'Order has invalid payment status '
                . $paymentIntent[Stripe::PARAM_STATUS] . ' but order is in ' . $order->getState()
                . ' state.'
            );

            $this->paymentsHelper->cancelOrder(
                $order,
                'Order #' . $order->getIncrementId()
                . ' canceled as payment status is ' . $paymentIntent[Stripe::PARAM_STATUS] . '.',
                BrippoOrder::STATUS_PAYMENT_FAILED, //toDo check real error type
            );
        } elseif ($order->getState() === Order::STATE_HOLDED) {
            /*
             * NORMAL BEHAVIOUR FOR ALLOWED PAYMENT FAILED ORDERS
             */
            return;
        } elseif (!$order->isCanceled()) {
            $error = 'Payment was not successful but order was not canceled. Current order state is '
                . $order->getState() . '.';
            $this->monitorPlatformService->sendNotification(
                ScopeInterface::SCOPE_STORE,
                $order->getStoreId(),
                $liveMode,
                $paymentIntentId,
                $order->getIncrementId(),
                $error
            );
            $this->logger->logOrderEvent(
                $order,
                $error . ' Notification was sent.'
            );
        }
    }

    /**
     * @param string $paymentId
     * @param OrderInterface $order
     * @param string $type
     * @param array $additionalInformation
     * @return void
     * @deprecated
     */
    public function reportOrderStatusChange(
        $paymentId,
        OrderInterface $order,
        string $type = PaymentIntents::TIMELINE_ITEM_TYPE_ORDER_STATUS,
        array $additionalInformation = []
    ): void
    {

        try {
            $payment = $order->getPayment();
            $liveMode = $payment->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_LIVEMODE
            );
            if (empty($liveMode)) {
                $liveMode = $this->dataHelper->isLiveMode($order->getStoreId());
            }
            $accountId = $this->dataHelper->getAccountId(
                $order->getStoreId(),
                $liveMode
            );

            if (empty($accountId)) {
                return;
            }

            $this->logger->logOrderEvent(
                $order,
                'Reporting status change...'
            );

            if (!empty($paymentId)) {
                $this->brippoApiPaymentIntents->reportTimelineStatus(
                    $liveMode ?? true,
                    $paymentId,
                    $order->getStatus(),
                    $type,
                    $this->dataHelper->getAccountId(
                        $order->getStoreId(),
                        $liveMode
                    ),
                    $order->getIncrementId(),
                    $additionalInformation
                );
                $this->logger->logOrderEvent(
                    $order,
                    'Reported status change for order: ' . $order->getStatus()
                );
            }
        } catch (Exception $ex) {
            $this->logger->logOrderEvent(
                $order,
                $ex->getMessage()
            );
        }
    }

    /**
     * @param OrderInterface $order
     * @param string $previousState
     * @param string $currentState
     * @return void
     */
    public function notifyAnomalousStateTransition(
        OrderInterface $order,
        string $previousState,
        string $currentState
    ): void
    {
        try {
            $payment = $order->getPayment();
            $paymentIntentId = $payment->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID
            );
            $paymentIntentId = is_array($paymentIntentId) ? reset($paymentIntentId) : $paymentIntentId;

            $liveMode = $payment->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_LIVEMODE
            );
            if (empty($liveMode)) {
                $liveMode = $this->dataHelper->isLiveMode($order->getStoreId());
            }

            $error = sprintf(
                'Anomalous order state transition detected: Order #%s changed from "%s" to "%s" without going through "processing" state.',
                $order->getIncrementId(),
                $previousState,
                $currentState
            );

            $this->monitorPlatformService->sendNotification(
                ScopeInterface::SCOPE_STORE,
                $order->getStoreId(),
                $liveMode,
                $paymentIntentId ?? '',
                $order->getIncrementId(),
                $error
            );

            $this->logger->logOrderEvent(
                $order,
                $error . ' Notification was sent.'
            );
        } catch (Exception $ex) {
            $this->logger->logOrderEvent(
                $order,
                'Failed to send anomalous state transition notification: ' . $ex->getMessage()
            );
        }
    }
}
