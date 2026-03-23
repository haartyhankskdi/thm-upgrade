<?php

namespace Ebizmarts\BrippoPayments\Controller\PaymentElement;

use Ebizmarts\BrippoPayments\Helper\Payments;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Sales\Api\OrderRepositoryInterface;
use Ebizmarts\BrippoPayments\Helper\PaymentElement as PaymentElementHelper;

class Cancel extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $orderRepository;
    protected $paymentsHelper;
    protected $paymentElementHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param Payments $paymentsHelper
     * @param PaymentElementHelper $paymentElementHelper
     */
    public function __construct(
        Context                     $context,
        JsonFactory                 $jsonFactory,
        Logger                      $logger,
        OrderRepositoryInterface    $orderRepository,
        Payments                    $paymentsHelper,
        PaymentElementHelper        $paymentElementHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->paymentsHelper = $paymentsHelper;
        $this->paymentElementHelper = $paymentElementHelper;
    }

    public function execute()
    {
        $this->paymentElementHelper->setParamsFromRequestBody($this->getRequest());

        $orderId = $this->getRequest()->getParam('orderId');
        $error = $this->getRequest()->getParam('error');

        try {
            $order = $this->orderRepository->get($orderId);
            $this->paymentsHelper->cancelOrder(
                $order,
                $error ?? 'Order cancelled due to frontend error.',
                BrippoOrder::STATUS_PAYMENT_FAILED
            );
            $this->paymentsHelper->restoreQuote($order);

            $response = [
                'valid' => 1
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
