<?php

namespace Ebizmarts\BrippoPayments\Observer;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Monitor;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod as PaymentMethodHelper;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\Config\Source\CaptureMethod;
use Ebizmarts\BrippoPayments\Model\Config\Source\CurrencyMode;
use Ebizmarts\BrippoPayments\Model\Express;
use Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement;
use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class OrderSaveAfter implements ObserverInterface
{
    protected $orderRepository;
    protected $dataHelper;
    protected $storeManager;
    protected $paymentsHelper;
    protected $logger;
    protected $brippoApiPaymentIntents;
    protected $monitor;
    protected $paymentMethodsHelper;
    protected $checkoutSession;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param DataHelper $dataHelper
     * @param StoreManagerInterface $storeManager
     * @param PaymentsHelper $paymentsHelper
     * @param Logger $logger
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param Monitor $monitor
     * @param PaymentMethodHelper $paymentMethodsHelper
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        DataHelper               $dataHelper,
        StoreManagerInterface    $storeManager,
        PaymentsHelper           $paymentsHelper,
        Logger                   $logger,
        BrippoPaymentIntentsApi  $brippoApiPaymentIntents,
        Monitor                  $monitor,
        PaymentMethodHelper      $paymentMethodsHelper,
        CheckoutSession          $checkoutSession
    ) {
        $this->orderRepository = $orderRepository;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        $this->paymentsHelper = $paymentsHelper;
        $this->logger = $logger;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->monitor = $monitor;
        $this->paymentMethodsHelper = $paymentMethodsHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();
            $scopeId = $this->storeManager->getStore()->getId();
            $payment = $order->getPayment();
            if (empty($payment)) {
                return;
            }

            $paymentMethodCode = $payment->getMethodInstance()->getCode();

            /*
             * REPORT ORDER STATUS TIMELINE
             */
            $isPaypal = strpos($paymentMethodCode, 'paypal_') === 0;
            if ($this->paymentMethodsHelper->isFrontendPaymentMethod($paymentMethodCode) || $isPaypal) {
                if ((!empty($order->getOrigData('status')) && $order->getOrigData('status') !== $order->getStatus())
                    || (!empty($order->getOrigData('state')) && $order->getOrigData('state') !== $order->getState())) {
                    if ($isPaypal) {
                        $this->reportPaypalOrderStatus($order);
                    } else {
                        $event = '';

                        if (!empty($order->getOrigData('status')) && $order->getOrigData('status') !== $order->getStatus()) {
                            $event .= 'Order status changed to '
                                . $order->getStatus()
                                . '.';
                        }

                        if (!empty($order->getOrigData('state')) && $order->getOrigData('state') !== $order->getState()) {
                            if ($event !== '') {
                                $event .= ' ';
                            }
                            $event .= 'Order state changed to '
                                . $order->getState()
                                . '.';
                        }

                        $this->logger->logOrderEvent(
                            $order,
                            $event
                        );

                        try {
                            BrippoOrder::updateTimeline($order);
                        } catch (Exception $ex) {
                            $this->logger->log('Unable to update Brippo order timeline: ' . $ex->getMessage());
                        }
                    }
                }
            }

            /*
             * Check if order went from directly to complete without going through processing
             */
            if (!empty($order->getOrigData('state')) && $order->getOrigData('state') !== $order->getState()) {
                $previousState = $order->getOrigData('state');
                $currentState = $order->getState();

                if (($previousState !== Order::STATE_PROCESSING)
                    && $currentState === Order::STATE_COMPLETE) {
                    try {
                        $this->monitor->notifyAnomalousStateTransition(
                            $order,
                            $previousState,
                            $currentState
                        );
                    } catch (Exception $ex) {
                        $this->logger->logOrderEvent(
                            $order,
                            'Error notifying anomalous state transition: ' . $ex->getMessage()
                        );
                    }
                }
            }

            /**
             * CHECK INVOICE ON ORDER STATUS FEATURE
             */
            if ($paymentMethodCode == Express::METHOD_CODE) {
                if ($this->dataHelper->getStoreConfig(
                    Express::XML_PATH_CAPTURE_METHOD,
                    $scopeId
                ) == CaptureMethod::ON_STATUS_CHANGE_CAPTURE) {
                    if ($order->getStatus() == $this->dataHelper->getStoreConfig(
                        Express::XML_PATH_STATUS_TRIGGERING_CAPTURE,
                        $scopeId
                    )) {
                        $this->captureAndInvoiceOrder($order);
                    }
                }
            } elseif ($paymentMethodCode == PaymentElement::METHOD_CODE) {
                if ($this->dataHelper->getStoreConfig(
                    PaymentElement::XML_PATH_CAPTURE_METHOD,
                    $scopeId
                ) == CaptureMethod::ON_STATUS_CHANGE_CAPTURE) {
                    if ($order->getStatus() == $this->dataHelper->getStoreConfig(
                        PaymentElement::XML_PATH_STATUS_TRIGGERING_CAPTURE,
                        $scopeId
                    )) {
                        $this->captureAndInvoiceOrder($order);
                    }
                }
            } elseif ($paymentMethodCode == ExpressCheckoutElement::METHOD_CODE) {
                if ($this->dataHelper->getStoreConfig(
                    ExpressCheckoutElement::XML_PATH_CAPTURE_METHOD,
                    $scopeId
                ) == CaptureMethod::ON_STATUS_CHANGE_CAPTURE) {
                    if ($order->getStatus() == $this->dataHelper->getStoreConfig(
                        ExpressCheckoutElement::XML_PATH_STATUS_TRIGGERING_CAPTURE,
                        $scopeId
                    )) {
                        $this->captureAndInvoiceOrder($order);
                    }
                }
            }
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $this->logger->logOrderEvent(
                $order,
                'Error during order save: ' . $ex->getMessage()
            );
        }
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Exception
     */
    private function captureAndInvoiceOrder($order)
    {
        if ($order->canInvoice()) {
            $this->logger->log('Capturing order #' . $order->getIncrementId()
                . ' due to status change trigger...');

            $payment = $order->getPayment();
            $paymentIntentId = $payment->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID
            );

            if (empty($paymentIntentId)) {
                throw new LocalizedException(__('Capture failed. Invalid Payment Intent Id.'));
            }

            $liveMode = $payment->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_LIVEMODE
            );
            $currency = $payment->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_CURRENCY
            );

            $amountToCapture = $order->getGrandTotal();
            $isConfiguredToUseBaseCurrency = $this->dataHelper->getStoreConfig(
                DataHelper::XML_PATH_CURRENCY_MODE,
                $order->getStore()->getId()
            ) === CurrencyMode::MODE_BASE_CURRENCY;
            if ($isConfiguredToUseBaseCurrency) {
                /*
                 * Amend for currency mode
                 */
                $amountToCapture = $order->getBaseGrandTotal();
            }

            $paymentIntent = $this->brippoApiPaymentIntents->capture(
                $paymentIntentId,
                $liveMode,
                $amountToCapture,
                $currency
            );

            if ($paymentIntent[Stripe::PARAM_STATUS] != Stripe::PAYMENT_INTENT_STATUS_SUCCEEDED) {
                throw new LocalizedException(
                    __('Capture failed. Payment Intent status is ' . $paymentIntent[Stripe::PARAM_STATUS] . '.')
                );
            }

            $this->logger->log('Order #' . $order->getIncrementId() .
                ' successfully captured online.');

            $this->paymentsHelper->invoiceOrder($order, $paymentIntentId);
        } else {
            $this->logger->log('Order #' . $order->getIncrementId() . ' can not be invoiced.');
        }
    }

    /**
     * @param Order $order
     * @return void
     * @deplacated Only used by paypal orders
     */
    private function reportOrderStatus(Order $order)
    {
        $this->monitor->reportOrderStatusChange(
            $order->getPayment()->getLastTransId(),
            $order,
            PaymentIntents::TIMELINE_ITEM_TYPE_ORDER_STATUS,
            [
                'Customer Email' => $order->getCustomerEmail()
            ]
        );
    }

    /**
     * @param Order $order
     * @return void
     * @deplacated Until we start using paypal transaction API
     */
    private function reportPaypalOrderStatus(Order $order)
    {
        $paymentId = $order->getPayment()->getAdditionalInformation('paypal_express_checkout_token');
        if (empty($paymentId)) {
            $paymentId = $order->getPayment()->getLastTransId();
        }
        $this->monitor->reportOrderStatusChange(
            $paymentId,
            $order,
            PaymentIntents::TIMELINE_ITEM_TYPE_ORDER_STATUS_PAYPAL,
            [
                'Customer Email' => $order->getCustomerEmail(),
                'Paypal Pending Reason' => $order->getPayment()->getAdditionalInformation('paypal_pending_reason'),
                'Paypal Order Id' => $order->getPayment()->getAdditionalInformation('paypal_express_checkout_token')
            ]
        );
    }
}
