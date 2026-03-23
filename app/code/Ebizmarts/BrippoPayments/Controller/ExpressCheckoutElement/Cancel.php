<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckoutElement;

use Ebizmarts\BrippoPayments\Helper\Payments;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Helper\ExpressCheckoutElement;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Sales\Api\OrderRepositoryInterface;

class Cancel extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $orderRepository;
    protected $paymentsHelper;
    protected $eceHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param Payments $paymentsHelper
     * @param ExpressCheckoutElement $eceHelper
     */
    public function __construct(
        Context                  $context,
        JsonFactory              $jsonFactory,
        Logger                   $logger,
        OrderRepositoryInterface $orderRepository,
        Payments                 $paymentsHelper,
        ExpressCheckoutElement     $eceHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->paymentsHelper = $paymentsHelper;
        $this->eceHelper = $eceHelper;
    }

    public function execute()
    {
        $this->eceHelper->setParamsFromRequestBody($this->getRequest());
        $orderId = $this->getRequest()->getParam('orderId');
        $orderIncrementId = $this->getRequest()->getParam('orderIncrementId');
        $error = $this->getRequest()->getParam('error');
        $this->logger->log('Trying to cancel order #' . $orderIncrementId . ': ' . $error);

        try {
            $order = $this->orderRepository->get($orderId);
            $this->paymentsHelper->cancelOrder(
                $order,
                $error,
                BrippoOrder::STATUS_PAYMENT_FAILED
            );
            $this->paymentsHelper->restoreQuote($order);
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
