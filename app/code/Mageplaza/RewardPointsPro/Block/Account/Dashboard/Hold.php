<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\RewardPointsPro\Block\Account\Dashboard;

use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPoints\Block\Account\Dashboard\Exchange;
use Mageplaza\RewardPoints\Model\Source\Status;

/**
 * Class Hold
 * @package Mageplaza\RewardPointsPro\Block\Account\Dashboard
 */
class Hold extends Exchange
{
    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getFirstHolding()
    {
        $currentDate           = $this->date->date();
        $transactionCollection = $this->collectionFactory->create()
            ->addFieldToFilter('status', Status::HOLDING)
            ->addFieldToFilter('reward_id', $this->getAccount()->getRewardId())
            ->addFieldToFilter('holding_date', ['notnull' => true])
            ->setOrder('holding_date', 'ASC');

        $transaction = $transactionCollection->getFirstItem();
        if (!$transaction->getData() || !$this->checkHoldTime($transaction)) {
            return [0, 0, 0, 0];
        }
        $points      = $transaction['point_amount'];
        $holdingDate = date('l, F j, Y', strtotime($transaction['holding_date']));
        $numberDays  = $this->calculateDays(strtotime($currentDate), strtotime($holdingDate));
        $dayLabel    = $numberDays > 1 ? __('days') : __('day');
        if ($numberDays < 0.5) {
            $dayLabel = 'today';
        }

        return [$this->formatPoint($points), $dayLabel, round($numberDays), $holdingDate];
    }

    /**
     * @param $transaction
     *
     * @return bool
     */
    protected function checkHoldTime($transaction)
    {
        if (strtotime($transaction->getHoldingDate()) <= strtotime($this->date->date())) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function canDisplay()
    {
        return $this->getEarningRate() || $this->getSpendingRate()
            || $this->getMaxPointPerCustomer()
            || (($this->getFirstExpire() === [0, 0, 0, 0]) ? null : $this->getFirstExpire())
            || (($this->getFirstHolding() === [0, 0, 0, 0]) ? null : $this->getFirstHolding());
    }
}
