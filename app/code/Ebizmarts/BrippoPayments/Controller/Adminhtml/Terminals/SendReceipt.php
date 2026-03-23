<?php

namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Terminals;

use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Ebizmarts\BrippoPayments\Helper\PlatformService\Receipts as PlatformReceiptsService;
use Magento\Store\Model\ScopeInterface;

class SendReceipt extends Action
{
    protected $dataHelper;
    protected $jsonFactory;
    protected $orderRepository;
    protected $platformServiceReceipts;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param JsonFactory $jsonFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param PlatformReceiptsService $platformServiceReceipts
     */
    public function __construct(
        Context                  $context,
        Data                     $dataHelper,
        JsonFactory              $jsonFactory,
        OrderRepositoryInterface $orderRepository,
        PlatformReceiptsService  $platformServiceReceipts
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->jsonFactory = $jsonFactory;
        $this->orderRepository = $orderRepository;
        $this->platformServiceReceipts = $platformServiceReceipts;
    }
    public function execute()
    {
        try {
            $requestBody = $this->dataHelper->unserializeRequestBody($this->getRequest());

            $paymentIntentId = isset($requestBody['paymentIntentId']) ? $requestBody['paymentIntentId'] : null;
            if (empty($paymentIntentId)) {
                throw new LocalizedException(__('Payment intent ID not found'));
            }

            $orderId = isset($requestBody['orderId']) ? $requestBody['orderId'] : null;
            if (empty($orderId)) {
                throw new LocalizedException(__('Order ID not found'));
            }

            $order = $this->orderRepository->get($orderId);
            if (empty($order) || empty($order->getEntityId())) {
                throw new LocalizedException(__('Order not found'));
            }

            $receiptNumber = $order->getPayment()->getAdditionalInformation(PaymentMethod::ADDITIONAL_DATA_RECEIPT_NUMBER);
            if (empty($receiptNumber)) {
                throw new LocalizedException(__('Receipt number not found for this order'));
            }

            $this->platformServiceReceipts->sendReceipt(
                ScopeInterface::SCOPE_STORE,
                $order->getStoreId(),
                $paymentIntentId,
                $order->getCustomerEmail(),
                $receiptNumber,
                $this->platformServiceReceipts->getReceiptDescription($order)
            );

            $history = $order->addStatusHistoryComment('Sent Brippo Receipt to ' . $order->getCustomerEmail(), $order->getStatus());
            $history->save();

            $this->messageManager->addSuccessMessage(__('Successfully sent Brippo receipt to ' . $order->getCustomerEmail()));

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
                $this->dataHelper->logger->log($ex->getMessage());
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

    protected function _isAllowed()
    {
        return true;
    }
}
