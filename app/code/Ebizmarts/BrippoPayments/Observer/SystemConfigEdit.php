<?php
/**
 * Copyright © 2018 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\BrippoPayments\Observer;

use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class SystemConfigEdit implements ObserverInterface
{
    const BRIPPO_MONITOR_JOB = "brippo_payments_monitor";

    private $_messageManager;
    /**
     * @var Data
     */
    private $helper;
    private $logger;

    public function __construct(
        Data $helper,
        ManagerInterface $messageManager,
        Logger $logger
    ) {
        $this->helper           = $helper;
        $this->_messageManager  = $messageManager;
        $this->logger           = $logger;
    }

    /**
     * Observer payment config section save to validate license and
     * check reporting api credentials.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            if (!$this->helper->isMonitorRunning()) {
                $this->_messageManager->addWarningMessage(__(
                    "Brippo Payments monitor cron is not running"
                ));
            }
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
        }
    }


}
