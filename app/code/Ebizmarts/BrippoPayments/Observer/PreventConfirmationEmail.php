<?php

namespace Ebizmarts\BrippoPayments\Observer;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;

class PreventConfirmationEmail implements ObserverInterface
{
    protected $quoteRepository;
    protected $logger;
    protected $paymentMethodHelper;

    public function __construct(
        QuoteRepository $quoteRepository,
        Logger $logger,
        PaymentMethod $paymentMethodHelper
    ) {
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->paymentMethodHelper = $paymentMethodHelper;
    }

    public function execute(Observer $observer)
    {
        /**
         * PREVENT CONFIRMATION EMAIL
         */

        try {
            $order = $observer->getData('order');
            if ($this->paymentMethodHelper->isFrontendPaymentMethod($order->getPayment()->getMethodInstance()->getCode())) {
                $order->setCanSendNewEmailFlag(false);
            }
        } catch (Exception $e) {
            $this->logger->log($e->getMessage());
            $this->logger->log($e->getTraceAsString());
        }
        return $this;
    }
}
