<?php

namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Terminals;

use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\TerminalBackend;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;

class ActionCompleted extends Action
{
    protected $dataHelper;
    protected $logger;
    protected $jsonFactory;
    protected $terminalBackendHelper;
    protected $orderRepository;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param Data $dataHelper
     * @param JsonFactory $jsonFactory
     * @param TerminalBackend $terminalBackendHelper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context                  $context,
        Logger                   $logger,
        Data                     $dataHelper,
        JsonFactory              $jsonFactory,
        TerminalBackend          $terminalBackendHelper,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->jsonFactory = $jsonFactory;
        $this->terminalBackendHelper = $terminalBackendHelper;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        try {
            $requestBody = $this->dataHelper->unserializeRequestBody($this->getRequest());

            $orderId = isset($requestBody['orderId']) ? $requestBody['orderId'] : null;
            if (empty($orderId)) {
                throw new LocalizedException(__('Order ID not found'));
            }

            $order = $this->orderRepository->get($orderId);
            if (empty($order) || empty($order->getEntityId())) {
                throw new LocalizedException(__('Order not found'));
            }

            $this->terminalBackendHelper->onActionCompleted($order);

            $response = [
                'valid' => 1
            ];
        } catch (Exception $ex) {
            if (!empty($order)) {
                $this->dataHelper->logger->logOrderEvent(
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

    protected function _isAllowed()
    {
        return true;
    }
}
