<?php

namespace Ebizmarts\BrippoPayments\Observer;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Payments;
use Ebizmarts\BrippoPayments\Helper\Webhook;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

class WebhookPaymentIntentCanceled implements ObserverInterface
{
    protected $logger;
    protected $webhookHelper;
    protected $orderInterface;
    protected $orderManagement;
    protected $quoteFactory;
    protected $paymentsHelper;

    /**
     * @param Logger $logger
     * @param Webhook $webhookHelper
     * @param OrderInterface $orderInterface
     * @param OrderManagementInterface $orderManagement
     * @param QuoteFactory $quoteFactory
     * @param Payments $paymentsHelper
     */
    public function __construct(
        Logger $logger,
        Webhook $webhookHelper,
        OrderInterface $orderInterface,
        OrderManagementInterface $orderManagement,
        QuoteFactory $quoteFactory,
        Payments $paymentsHelper
    ) {
        $this->logger = $logger;
        $this->webhookHelper = $webhookHelper;
        $this->orderInterface = $orderInterface;
        $this->orderManagement = $orderManagement;
        $this->quoteFactory = $quoteFactory;
        $this->paymentsHelper = $paymentsHelper;
    }

    public function execute(Observer $observer)
    {
        /**
         * HANDLE PAYMENT INTENT CANCELLED EVENT
         */

        try {
            $webhookData = $observer->getData('webhookData');
            $order = null;

            $orderIncrementId = $this->webhookHelper->getOrderIncrementIdFromEvent($webhookData);
            if (!empty($orderIncrementId)) {
                $order = $this->orderInterface->loadByIncrementId($orderIncrementId);
            } else {
                $quoteId = $this->webhookHelper->getQuoteIdFromEvent($webhookData);
                if (!empty($quoteId)) {
                    $quote = $this->quoteFactory->create()->load($quoteId);
                    $orderIncrementId = $quote->getReservedOrderId();
                    $order = $this->orderInterface->loadByIncrementId($orderIncrementId);
                } else {
                    throw new LocalizedException(__('Can not get Order ID nor Quote ID from event.'));
                }
            }

            if ($order && !empty($order->getEntityId())) {
                if (!$order->isCanceled() && !$order->hasInvoices()) {
                    $order->getPayment()->setAdditionalInformation(
                        PaymentMethod::ADDITIONAL_DATA_STATUS,
                        $webhookData['data']['object']['status']
                    )->save();
                    $this->paymentsHelper->cancelOrder(
                        $order,
                        'Pending order #' . $order->getIncrementId()
                        . ' cancelled as payment intent was cancelled.'
                    );
                }
            } else {
                $this->logger->log("Unable to find order associated to this event.");
            }
        } catch (Exception $e) {
            $this->logger->log($e->getMessage());
        }
    }
}
