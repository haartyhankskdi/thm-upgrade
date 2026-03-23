<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\RewardPointsPro\Helper\Data;

/**
 * Class HoldingPoints
 * @package Mageplaza\RewardPointsPro\Observer
 */
class HoldingPoints implements ObserverInterface
{
    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * HoldingPoints constructor.
     *
     * @param WriterInterface $configWriter
     * @param Data $helperData
     */
    public function __construct(
        WriterInterface $configWriter,
        Data $helperData
    ) {
        $this->configWriter = $configWriter;
        $this->helperData   = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $order       = $observer->getEvent()->getOrder();
        $pointAmount = $observer->getEvent()->getPointAmount();
        if ($this->helperData->getConfigEarning('hold_points', $order->getStoreId())) {
            if ($order->getMpRewardEarn() > 0 && !$order->getMpRewardHold()) {
                $order->setMpRewardHold(1);
                $this->helperData->addTransaction(
                    Data::ACTION_HOLDING_POINTS,
                    $order->getCustomerId(),
                    $pointAmount,
                    $order
                );
                $this->configWriter->save(Data::CONFIG_TRANS_HOLD_PATH, 1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            }
        }
    }
}
