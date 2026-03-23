<?php

namespace Ebizmarts\BrippoPayments\Observer;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Webhook;
use Ebizmarts\BrippoPayments\Model\PayByLink;
use Ebizmarts\BrippoPayments\Model\PayByLinkMoto;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Ebizmarts\BrippoPayments\Helper\PayByLink as PayByLinkHelper;

class WebhookCheckoutSessionCompleted implements ObserverInterface
{
    protected $logger;
    protected $webhookHelper;
    protected $orderInterface;
    protected $payByLinkHelper;

    /**
     * @param Logger $logger
     * @param Webhook $webhookHelper
     * @param OrderInterface $orderInterface
     * @param PayByLinkHelper $payByLinkHelper
     */
    public function __construct(
        Logger                   $logger,
        Webhook                  $webhookHelper,
        OrderInterface           $orderInterface,
        PayByLinkHelper $payByLinkHelper
    ) {
        $this->logger = $logger;
        $this->webhookHelper = $webhookHelper;
        $this->orderInterface = $orderInterface;
        $this->payByLinkHelper = $payByLinkHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * HANDLE CHECKOUT SESSION COMPLETED EVENT
         */
        try {
            $webhookData = $observer->getData('webhookData');
            $paymentIntentId = $webhookData['data']['object']['payment_intent'];
            $paymentLinkId = $webhookData['data']['object']['payment_link'];
            $liveMode = $webhookData['data']['object']['livemode'];

            $order = null;
            $orderIncrementId = $this->webhookHelper->getOrderIncrementIdFromEvent($webhookData);
            if (!empty($orderIncrementId)) {
                $order = $this->orderInterface->loadByIncrementId($orderIncrementId);
            }

            if ($order && !empty($order->getEntityId())) {
                $this->logger->log("Checkout session completed for order #" . $order->getIncrementId());

                $payment = $order->getPayment();
                $paymentMethodCode = $payment->getMethodInstance()->getCode();
                if ($paymentMethodCode === PayByLink::METHOD_CODE
                    || $paymentMethodCode === PayByLinkMoto::METHOD_CODE) {
                    $this->payByLinkHelper->processPayByLinkPaymentCompleted(
                        $order,
                        $paymentIntentId,
                        $liveMode,
                        $paymentMethodCode,
                        $paymentLinkId
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
