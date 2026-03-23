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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\RewardPointsPro\Helper\Data;

/**
 * Class HoldingDate
 * @package Mageplaza\RewardPointsPro\Observer
 */
class HoldingDate implements ObserverInterface
{
    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * HoldingDate constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        DateTime $date,
        Data $helperData
    ) {
        $this->date       = $date;
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $transaction = $observer->getEvent()->getDataObject();
        if (($numberOfDays = $this->helperData->getConfigEarning('hold_points', $transaction->getStoreId()))
            && $transaction->getActionCode() === Data::ACTION_HOLDING_POINTS
        ) {
            $transaction->setHoldingDate(date('Y-m-d H:i:s', strtotime("+{$numberOfDays}days", strtotime($this->date->date()))))->save();
        }
    }
}
