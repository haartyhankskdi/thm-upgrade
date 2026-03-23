<?php

namespace Ebizmarts\BrippoPayments\Observer;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod as PaymentMethodHelper;
use Ebizmarts\BrippoPayments\Helper\Payments;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class CancelIncompleteOrder implements ObserverInterface
{
    /** @var CheckoutSession */
    protected $checkoutSession;

    /** @var Payments */
    protected $paymentsHelper;

    /** @var PaymentMethodHelper */
    protected $paymentMethodsHelper;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var Logger */
    protected $logger;
    protected $brippoApiPaymentIntents;
    protected $dataHelper;

    public function __construct(
        CheckoutSession          $checkoutSession,
        Payments                 $paymentsHelper,
        Logger                   $logger,
        OrderRepositoryInterface $orderRepository,
        PaymentMethodHelper      $paymentMethodsHelper,
        BrippoPaymentIntentsApi  $brippoApiPaymentIntents,
        Data                     $dataHelper
    ) {
        $this->paymentsHelper = $paymentsHelper;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->paymentMethodsHelper = $paymentMethodsHelper;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer)
    {
        try {
            $controller = $observer->getControllerAction();
            if (!empty($controller)
                && method_exists($controller, 'getRequest')
                && is_callable([$controller, 'getRequest'])
            ) {
                $request = $controller->getRequest();
                if (!empty($request)
                    && method_exists($request, 'getRouteName')
                    && is_callable([$request, 'getRouteName'])
                ) {
                    $route = $request->getRouteName();
                } else {
                    $route = null;
                }

                if (!empty($request)
                    && method_exists($request, 'getActionName')
                    && is_callable([$request, 'getActionName'])
                ) {
                    $actionName = $request->getActionName();
                } else {
                    $actionName = null;
                }
            } else {
                $route = null;
                $actionName = null;
            }

            if (($route === 'cms' && $actionName === 'index')
                || ($route === 'catalog' && $actionName === 'view')
                || ($route === 'checkout' && $actionName === 'index')
            ) {
                $orderId = $this->checkoutSession->getLastOrderId();
                if (empty($orderId)) {
                    return;
                }
                $order = $this->orderRepository->get($orderId);
                if (!empty($order)
                    && !empty($order->getPayment())
                    && $this->paymentMethodsHelper->isFrontendPaymentMethod($order->getPayment()->getMethod())
                    && $order->getState() === Order::STATE_NEW
                    && $order->getStatus() === BrippoOrder::STATUS_PENDING) {
                    try {
                        if (!empty($order->getPayment())) {
                            $paymentIntentId = $order->getPayment()->getAdditionalInformation(
                                PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID
                            );
                            $liveMode = $order->getPayment()->getAdditionalInformation(
                                PaymentMethod::ADDITIONAL_DATA_LIVEMODE
                            );
                            if (empty($liveMode)) {
                                $liveMode = $this->dataHelper->isLiveMode($order->getStoreId());
                            }

                            $paymentIntent = $this->brippoApiPaymentIntents->get($paymentIntentId, $liveMode);
                            $paymentIntentStatus = $paymentIntent[Stripe::PARAM_STATUS];
                            if ($paymentIntentStatus === Stripe::PAYMENT_INTENT_STATUS_SUCCEEDED
                                || $paymentIntentStatus === Stripe::PAYMENT_INTENT_STATUS_REQUIRES_CAPTURE) {
                                /*
                                 * PAYMENT SEEMS TO BE OK
                                 * ABORT CANCELLATION
                                 */
                                $this->logger->log('Payment status is ' . $paymentIntentStatus
                                    . ' after page refresh for order #' . $order->getIncrementId()
                                    . '. Cancellation aborted.');
                                return;
                            }
                        }
                    } catch (Exception $ex) {
                        $this->logger->log('Trying to check payment for incomplete order after refresh. '
                            . 'Can not get payment for order #' . $order->getIncrementId() . ': ' . $ex->getMessage());
                    }

                    $this->paymentsHelper->cancelOrder(
                        $order,
                        'Customer refreshed during payment authentication process',
                        BrippoOrder::STATUS_PAYMENT_FAILED
                    );
                    $this->paymentsHelper->restoreQuote($order);
                    $this->logger->log('Canceled order #' . $order->getIncrementId()
                        . ' as the customer seems to have refreshed during payment process. Quote was restored.');
                }
            }
        } catch (Exception $e) {
            $this->logger->log("Failed to reset unique order id, error: ". $e->getMessage());
        }
    }
}
