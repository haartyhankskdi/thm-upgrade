<?php

namespace Ebizmarts\SagePaySuite\Observer;

use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;

class OrderState implements ObserverInterface
{
    public const SAGE_PAY_PREFIX = 'sagepaysuite';

    /** @var Logger $sagePayLogger */
    private $sagePayLogger;

    /**
     * @param Logger $sagePayLogger
     */
    public function __construct(
        Logger $sagePayLogger
    ) {
        $this->sagePayLogger = $sagePayLogger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Order|OrderInterface $order */
        $order = $observer->getData('order');
        if ($order) {
            /** @var OrderPaymentInterface $payment */
            $payment = $order->getPayment();
            if ($payment) {
                if ($this->isSagePayPayment($payment->getMethod())) {
                    $this->logOrderData($order, $payment);
                    $this->logPaymentAdditionalInformation($payment->getAdditionalInformation());
                    $this->logStackTraceAsString();
                }
            }
        }
    }

    /**
     * @param string $paymentMethod
     * @return bool
     */
    public function isSagePayPayment($paymentMethod)
    {
        return strpos($paymentMethod, self::SAGE_PAY_PREFIX) !== false;
    }

    /**
     * @param Order|OrderInterface $order
     * @param OrderPaymentInterface $payment
     * @return void
     */
    private function logOrderData($order, $payment)
    {
        $this->sagePayLogger->sageLog(
            Logger::LOG_ORDER_STATE,
            'Payment method: ' . $payment->getMethod()
            . ' - Increment order ID: ' . $order->getIncrementId()
            . ' - Order State: ' . $order->getState()
            . ' - Order Status: ' . $order->getStatus(),
            [__METHOD__, __LINE__]
        );
    }

    /**
     * @return void
     */
    private function logStackTraceAsString()
    {
        $stackTrace = new \Exception();
        $this->sagePayLogger->sageLog(
            Logger::LOG_ORDER_STATE,
            $stackTrace->getTraceAsString(),
            [__METHOD__, __LINE__]
        );
    }

    /**
     * @param string[] $additionalInformation
     * @return void
     */
    private function logPaymentAdditionalInformation($additionalInformation)
    {
        $this->sagePayLogger->sageLog(
            Logger::LOG_ORDER_STATE,
            $additionalInformation,
            [__METHOD__, __LINE__]
        );
    }
}
