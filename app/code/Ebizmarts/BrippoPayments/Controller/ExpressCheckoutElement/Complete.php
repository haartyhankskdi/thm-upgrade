<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckoutElement;

use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Helper\ExpressCheckoutElement;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;

class Complete extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $checkoutSession;
    protected $orderRepository;
    protected $paymentsHelper;
    protected $dataHelper;
    protected $orderSender;
    protected $eceHelper;
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentsHelper $paymentsHelper
     * @param OrderSender $orderSender
     * @param ExpressCheckoutElement $eceHelper
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Context                     $context,
        JsonFactory                 $jsonFactory,
        Logger                      $logger,
        DataHelper                  $dataHelper,
        CheckoutSession             $checkoutSession,
        OrderRepositoryInterface    $orderRepository,
        PaymentsHelper              $paymentsHelper,
        OrderSender                 $orderSender,
        ExpressCheckoutElement      $eceHelper,
        UrlInterface                $urlBuilder
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->paymentsHelper = $paymentsHelper;
        $this->orderSender = $orderSender;
        $this->eceHelper = $eceHelper;
        $this->urlBuilder = $urlBuilder;
    }

    public function execute()
    {
        try {
            $this->eceHelper->setParamsFromRequestBody($this->getRequest());
            $orderId = $this->getRequest()->getParam('orderId');
            $orderIncrementId = $this->getRequest()->getParam('orderIncrementId');
            $paymentIntentId = $this->getRequest()->getParam('paymentIntentId');
            $paymentIntentStatus = $this->getRequest()->getParam('paymentIntentStatus');
            $isOrderRecover = $this->getRequest()->getParam("isOrderRecover");


            $this->logger->log('Trying to complete Express Checkout Element for Order #' . $orderIncrementId .
                ' with Payment Intent ' . $paymentIntentId . ' and status ' . $paymentIntentStatus . '...');

            $order = $this->orderRepository->get($orderId);
            if ($order && !empty($order->getEntityId())) {
                $this->logger->logOrderEvent(
                    $order,
                    'Trying to complete with Payment Intent ' . $paymentIntentId . ' and status ' . $paymentIntentStatus . '...'
                );

                if ($paymentIntentStatus === Stripe::PAYMENT_INTENT_STATUS_SUCCEEDED) {
                    if ($isOrderRecover
                        || $order->getState() == Order::STATE_CANCELED) {
                        $this->paymentsHelper->recoverOrder($order, \Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement::METHOD_CODE);
                    }
                    $this->paymentsHelper->invoiceOrder($order, $paymentIntentId);
                } elseif ($paymentIntentStatus == Stripe::PAYMENT_INTENT_STATUS_REQUIRES_CAPTURE) {
                    if ($isOrderRecover
                        || $order->getState() == Order::STATE_CANCELED) {
                        $this->paymentsHelper->recoverOrder($order, \Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement::METHOD_CODE);
                    }
                    $this->paymentsHelper->processUncapturedPaymentOrder($order);
                } else {
                    if (!$isOrderRecover) {
                        $this->paymentsHelper->cancelOrder(
                            $order,
                            'Trying to complete order but payment status is ' . $paymentIntentStatus,
                            BrippoOrder::STATUS_PAYMENT_FAILED
                        );
                        $this->paymentsHelper->restoreQuote($order);
                    }
                    throw new LocalizedException(__('Invalid payment status: ' . $paymentIntentStatus
                        . '. Please try again.'));
                }

                $this->checkoutSession
                    ->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastSuccessQuoteId($order->getQuoteId())
                    ->setLastQuoteId($order->getQuoteId())
                    ->setLastOrderStatus($order->getStatus());

                $this->logger->logOrderEvent(
                    $order,
                    'Order successfully completed.'
                );
            } else {
                throw new LocalizedException(__('Unable to find order with id ' . $orderId));
            }

            $response = [
                'valid' => 1,
                'url' => $this->urlBuilder->getUrl('checkout/onepage/success')
            ];
        } catch (Exception $ex) {
            if ($order) {
                $this->logger->logOrderEvent(
                    $order,
                    $ex->getMessage()
                );
            } else {
                $this->logger->log($ex->getMessage());
            }
            $this->messageManager->addErrorMessage($ex->getMessage());
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
