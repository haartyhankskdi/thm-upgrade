<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckout;

use Ebizmarts\BrippoPayments\Helper\Payments;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;
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
    protected $expressHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param Payments $paymentsHelper
     * @param ExpressHelper $expressHelper
     */
    public function __construct(
        Context                  $context,
        JsonFactory              $jsonFactory,
        Logger                   $logger,
        OrderRepositoryInterface $orderRepository,
        Payments                 $paymentsHelper,
        ExpressHelper              $expressHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->paymentsHelper = $paymentsHelper;
        $this->expressHelper = $expressHelper;
    }

    public function execute()
    {
        $this->expressHelper->setParamsFromRequestBody($this->getRequest());
        $orderId = $this->getRequest()->getParam('orderId');
        $error = $this->getRequest()->getParam('error');
        $this->logger->log('Trying to cancel order ' . $orderId . ' due to: ');
        // phpcs:disable
        $this->logger->log(print_r($error, true));
        // phpcs:enable

        try {
            $order = $this->orderRepository->get($orderId);

            $this->paymentsHelper->cancelOrder(
                $order,
                'Order cancelled due to frontend error.',
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
