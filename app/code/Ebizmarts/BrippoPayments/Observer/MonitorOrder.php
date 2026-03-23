<?php

namespace Ebizmarts\BrippoPayments\Observer;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Monitor;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod as PaymentMethodHelper;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class MonitorOrder implements ObserverInterface
{
    protected $logger;
    protected $monitorHelper;
    protected $paymentMethodHelper;

    public function __construct(
        Logger $logger,
        Monitor $monitorHelper,
        PaymentMethodHelper $paymentMethodHelper
    ) {
        $this->logger = $logger;
        $this->monitorHelper = $monitorHelper;
        $this->paymentMethodHelper = $paymentMethodHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var OrderInterface $order */
            $order = $observer->getData('order');

            $payment = $order->getPayment();
            if (!empty($payment) && $this->paymentMethodHelper->isFrontendPaymentMethod($payment->getMethod())
            ) {
                $this->monitorHelper->checkOrder($order);
            }
        } catch (Exception $ex) {
            $this->logger->logOrderEvent(
                $order,
                $ex->getMessage() . ' ' . $ex->getTraceAsString()
            );
        }
    }
}
