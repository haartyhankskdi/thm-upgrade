<?php

namespace Ebizmarts\BrippoPayments\Observer;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod as PaymentMethodHelper;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CheckoutSuccessPage implements ObserverInterface
{
    /** @var CheckoutSession */
    protected $checkoutSession;

    /** @var PaymentMethodHelper */
    protected $paymentMethodHelper;

    /** @var Logger */
    protected $logger;

    public function __construct(
        CheckoutSession $checkoutSession,
        PaymentMethodHelper $paymentMethodHelper,
        Logger $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->paymentMethodHelper = $paymentMethodHelper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $this->paymentMethodHelper->resetBrippoOrderUniqId($this->checkoutSession);
            $this->paymentMethodHelper->generateOrderUniqId($this->checkoutSession);
        } catch (Exception $e) {
            $this->logger->log("Failed to reset unique order id, error: ". $e->getMessage());
        }
    }
}
