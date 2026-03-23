<?php

namespace Ebizmarts\BrippoPayments\Observer;

use Ebizmarts\BrippoPayments\Model\PayByLinkMoto;
use Ebizmarts\BrippoPayments\Model\TerminalBackend;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ebizmarts\BrippoPayments\Helper\PayByLinkBackend;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Setup\Exception;

class OrderPlaceAfter implements ObserverInterface
{
    protected $payByLinkMotoHelper;
    protected $terminalBackendHelper;
    protected $logger;

    /**
     * @param PayByLinkBackend $payByLinkMotoHelper
     * @param \Ebizmarts\BrippoPayments\Helper\TerminalBackend $terminalBackendHelper
     * @param Logger $logger
     */
    public function __construct(
        PayByLinkBackend                                 $payByLinkMotoHelper,
        \Ebizmarts\BrippoPayments\Helper\TerminalBackend $terminalBackendHelper,
        Logger                                           $logger
    ) {
        $this->payByLinkMotoHelper = $payByLinkMotoHelper;
        $this->terminalBackendHelper = $terminalBackendHelper;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();

            /*
             * PAY BY LINK MOTO
             */
            if ($order->getPayment()->getMethodInstance()->getCode() === PayByLinkMoto::METHOD_CODE) {
                $this->payByLinkMotoHelper->processBackendOrder($order);
            }

            /*
             * BRIPPO TERMINAL BACKEND
             */
            if ($order->getPayment()->getMethodInstance()->getCode() === TerminalBackend::METHOD_CODE) {
                $this->terminalBackendHelper->onPlaceOrder($order);
            }
        } catch (Exception $e) {
            $this->logger->log($e->getMessage());
            $this->logger->log($e->getTraceAsString());
        }
    }
}
