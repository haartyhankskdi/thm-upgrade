<?php

namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Order;

use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;

class InvoiceOffline extends Action
{
    protected $dataHelper;
    protected $logger;
    protected $jsonFactory;
    protected $orderRepository;
    protected $invoiceService;
    protected $invoiceSender;
    protected $transaction;
    protected $invoiceRepository;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param Data $dataHelper
     * @param JsonFactory $jsonFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        Context $context,
        Logger $logger,
        Data $dataHelper,
        JsonFactory $jsonFactory,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->jsonFactory = $jsonFactory;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transaction = $transaction;
        $this->invoiceRepository = $invoiceRepository;
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

            $this->logger->logOrderEvent(
                $order,
                'Starting offline invoice process...'
            );

            // Check if order can be invoiced with detailed error messages
            $canInvoiceResult = $this->canOrderBeInvoiced($order);
            if (!$canInvoiceResult['canInvoice']) {
                // Check if there's already a pending invoice
                if ($this->dataHelper->isOrderInvoicePending($order)) {
                    $this->logger->logOrderEvent(
                        $order,
                        'Found pending invoice, attempting to set it as paid...'
                    );

                    $this->setPendingInvoiceToPaid($order);
                    $this->setOrderToProcessing($order);

                    $this->logger->logOrderEvent(
                        $order,
                        'Successfully set pending invoice to paid and order to processing'
                    );
                } elseif ($this->hasOrderBeenInvoiced($order)) {
                    // Order already has paid invoices, just move to processing
                    $this->setOrderToProcessing($order);
                } else {
                    throw new LocalizedException(__('Order cannot be invoiced: %1', $canInvoiceResult['reason']));
                }
            } else {
                $this->logger->logOrderEvent(
                    $order,
                    'Creating new offline invoice...'
                );

                $this->createOfflineInvoice($order);
                $this->setOrderToProcessing($order);

                $this->logger->logOrderEvent(
                    $order,
                    'Successfully created offline invoice and set order to processing'
                );
            }

            $response = [
                'valid' => 1,
                'message' => 'Invoice processed successfully'
            ];
        } catch (Exception $ex) {
            $response = [
                'valid' => 0,
                'message' => $ex->getMessage()
            ];
            $this->logger->logOrderEvent(
                !empty($order) ? $order : null,
                'Error processing invoice: ' . $ex->getMessage()
            );
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }

    /**
     * Create offline invoice for the order
     *
     * @param $order
     * @throws LocalizedException
     */
    protected function createOfflineInvoice($order)
    {
        try {
            // Create invoice
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
            $invoice->register();

            // Save invoice and update order
            $transactionSave = $this->transaction->addObject($invoice)->addObject($order);
            $transactionSave->save();

            // Send invoice email if configured
            try {
                $this->invoiceSender->send($invoice);
            } catch (Exception $e) {
                $this->logger->logOrderEvent(
                    $order,
                    'Failed to send invoice email: ' . $e->getMessage()
                );
            }
        } catch (Exception $e) {
            throw new LocalizedException(__('Failed to create offline invoice: %1', $e->getMessage()));
        }
    }

    /**
     * Set pending invoice to paid status
     *
     * @param $order
     * @throws LocalizedException
     */
    protected function setPendingInvoiceToPaid($order)
    {
        try {
            $invoices = $order->getInvoiceCollection();
            $invoices->setOrder('created_at', 'DESC');

            foreach ($invoices as $invoice) {
                if ($invoice->getState() == Invoice::STATE_OPEN) {
                    $invoice->setState(Invoice::STATE_PAID);
                    $invoice->pay();
                    $this->invoiceRepository->save($invoice);

                    $this->logger->logOrderEvent(
                        $order,
                        'Set invoice #' . $invoice->getIncrementId() . ' to paid status'
                    );
                    break; // Only process the first pending invoice
                }
            }
        } catch (Exception $e) {
            throw new LocalizedException(__('Failed to set pending invoice to paid: %1', $e->getMessage()));
        }
    }

    /**
     * Set order status to processing
     *
     * @param $order
     * @throws LocalizedException
     */
    protected function setOrderToProcessing($order)
    {
        try {
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus(Order::STATE_PROCESSING);
            $order->addCommentToStatusHistory('Order set to processing via Brippo offline invoice');
            $this->orderRepository->save($order);
        } catch (Exception $e) {
            throw new LocalizedException(__('Failed to set order to processing: %1', $e->getMessage()));
        }
    }

    /**
     * Check if order can be invoiced with detailed error messages
     * Based on Magento's Order::canInvoice() method
     *
     * @param $order
     * @return array
     */
    protected function canOrderBeInvoiced($order): array
    {
        // Check if order can be unhold or is in payment review
        if ($order->canUnhold()) {
            return [
                'canInvoice' => false,
                'reason' => 'Order is on hold and needs to be unheld first'
            ];
        }

        if ($order->isPaymentReview()) {
            return [
                'canInvoice' => false,
                'reason' => 'Order is under payment review'
            ];
        }

        $state = $order->getState();

        // Check order state
        if ($order->isCanceled()) {
            return [
                'canInvoice' => false,
                'reason' => 'Order is canceled'
            ];
        }

        if ($state === Order::STATE_COMPLETE) {
            return [
                'canInvoice' => false,
                'reason' => 'Order is already complete'
            ];
        }

        if ($state === Order::STATE_CLOSED) {
            return [
                'canInvoice' => false,
                'reason' => 'Order is closed'
            ];
        }

        // Check action flag
        if ($order->getActionFlag(Order::ACTION_FLAG_INVOICE) === false) {
            return [
                'canInvoice' => false,
                'reason' => 'Invoice action is disabled for this order'
            ];
        }

        // Check if there are items that can be invoiced
        $hasItemsToInvoice = false;
        foreach ($order->getAllItems() as $item) {
            if ($item->getQtyToInvoice() > 0 && !$item->getLockedDoInvoice()) {
                $hasItemsToInvoice = true;
                break;
            }
        }

        if (!$hasItemsToInvoice) {
            return [
                'canInvoice' => false,
                'reason' => 'No items available to invoice (all items may already be invoiced or locked)'
            ];
        }

        return [
            'canInvoice' => true,
            'reason' => ''
        ];
    }

    /**
     * Check if order has been invoiced (has paid invoices)
     *
     * @param $order
     * @return bool
     */
    protected function hasOrderBeenInvoiced($order): bool
    {
        $invoices = $order->getInvoiceCollection();

        foreach ($invoices as $invoice) {
            if ($invoice->getState() == Invoice::STATE_PAID) {
                return true;
            }
        }
        
        return false;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
    }
}
