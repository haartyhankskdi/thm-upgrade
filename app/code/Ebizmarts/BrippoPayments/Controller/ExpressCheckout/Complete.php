<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckout;

use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;

class Complete extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $checkoutSession;
    protected $orderRepository;
    protected $paymentsHelper;
    protected $dataHelper;
    protected $orderSender;
    protected $expressHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentsHelper $paymentsHelper
     * @param OrderSender $orderSender
     * @param ExpressHelper $expressHelper
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
        ExpressHelper               $expressHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->paymentsHelper = $paymentsHelper;
        $this->orderSender = $orderSender;
        $this->expressHelper = $expressHelper;
    }

    public function execute()
    {
        try {
            $this->expressHelper->setParamsFromRequestBody($this->getRequest());
            $orderId = $this->getRequest()->getParam('orderId');
            $orderIncrementId = $this->getRequest()->getParam('orderIncrementId');
            $paymentIntentId = $this->getRequest()->getParam('paymentIntentId');
            $status = $this->getRequest()->getParam('status');

            $this->logger->log('Trying to complete Express Checkout for Order #' . $orderIncrementId .
                ' with Payment Intent ' . $paymentIntentId . '...');

            $order = $this->orderRepository->get($orderId);
            if ($order && !empty($order->getEntityId())) {
                if ($order->canInvoice() && $status == Stripe::PAYMENT_INTENT_STATUS_SUCCEEDED) {
                    $this->paymentsHelper->invoiceOrder($order, $paymentIntentId);
                } elseif ($status == Stripe::PAYMENT_INTENT_STATUS_REQUIRES_CAPTURE) {
                    $this->paymentsHelper->processUncapturedPaymentOrder($order);
                }

                $this->checkoutSession
                    ->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastSuccessQuoteId($order->getQuoteId())
                    ->setLastQuoteId($order->getQuoteId())
                    ->setLastOrderStatus($order->getStatus());

                $this->logger->log('Order #' . $order->getIncrementId() . ' completed successfully.');
            } else {
                throw new LocalizedException(__('Unable to find order with id ' . $orderId));
            }

            $response = [
                'valid' => 1
            ];
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
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
