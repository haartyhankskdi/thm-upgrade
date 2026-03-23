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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Block\Account\Dashboard;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use Mageplaza\RewardPoints\Block\Account\Dashboard;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Rate;
use Mageplaza\RewardPoints\Model\Source\ActionType;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Mageplaza\RewardPoints\Model\Source\Status;

/**
 * Class Exchange
 * @package Mageplaza\RewardPoints\Block\Account\Dashboard
 */
class Exchange extends Dashboard
{
    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     *  Exchange Constructor.
     *
     * @param DateTime $date
     * @param CollectionFactory $collectionFactory
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        DateTime $date,
        CollectionFactory $collectionFactory,
        Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->date              = $date;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $helper, $data);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function canDisplay()
    {
        return $this->getEarningRate() || $this->getSpendingRate()
            || $this->getMaxPointPerCustomer()
            || (($this->getFirstExpire() === [0, 0, 0, 0]) ? null : $this->getFirstExpire());
    }

    /**
     * Get max point per customer
     * @return int
     */
    public function getMaxPointPerCustomer()
    {
        return $this->helper->getMaxPointPerCustomer();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getFirstExpire()
    {
        $currentDate           = $this->date->date();
        $transactionCollection = $this->collectionFactory->create()
            ->addFieldToFilter('status', Status::COMPLETED)
            ->addFieldToFilter('action_code', ['in' => [Data::ACTION_ADMIN, Data::ACTION_EARNING_ORDER]])
            ->addFieldToFilter('expiration_date', ['notnull' => true])
            ->addFieldToFilter('expiration_date', ['from' => $currentDate])
            ->addFieldToFilter('reward_id', $this->getAccount()->getRewardId())
            ->setOrder('expiration_date', 'ASC');
        $transactionCollection->getSelect()->where('main_table.point_remaining > main_table.point_used');
        $transaction = $transactionCollection->getFirstItem();
        if (!$transaction->getData() || !$this->checkExpireTime($transaction)) {
            return [0, 0, 0, 0];
        }

        $pointRemaining = $transaction['point_remaining'];
        $pointUsed      = $transaction['point_used'];
        $createdDate    = $transaction['created_at'];
        $expireDate     = $transaction['expiration_date'];
        $points         = $pointRemaining - $pointUsed;

        if ($points > 0) {
            $expiredDay       = (int) round($this->calculateDays(strtotime($transaction['created_at']), strtotime($transaction['expiration_date'])));

            $numberDaysExpire = $this->calculateDays(strtotime($currentDate), strtotime($expireDate));
            $expirationDate   = date('l, F j, Y', (strtotime("+{$expiredDay}days", strtotime($createdDate))));
            $dayLabel         = $numberDaysExpire > 1 ? __('days') : __('day');

            if ($numberDaysExpire < 0.5) {
                $dayLabel = 'today';
            }

            return [$points, $dayLabel, round($numberDaysExpire), $expirationDate];
        }

        return [0, 0, 0, 0];
    }

    /**
     * @param $transaction
     *
     * @return bool
     */
    protected function checkExpireTime($transaction)
    {
        if (!$this->helper->getSalesPointExpiredAfter($transaction->getStoreId())) {
            return false;
        }

        if (strtotime($transaction->getExpirationDate()) <= strtotime($this->date->date())) {
            return false;
        }

        return true;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return float|int
     */
    public function calculateDays($startDate, $endDate)
    {
        $numberOfDays = ($endDate - $startDate) / (60 * 60 * 24);

        return $numberOfDays;
    }

    /**
     * Get the earning rate
     *
     * @return Rate|null
     * @throws NoSuchEntityException
     */
    public function getEarningRate()
    {
        return $this->getRate(ActionType::EARNING);
    }

    /**
     * Get the spending rate
     *
     * @return Rate|null
     * @throws NoSuchEntityException
     */
    public function getSpendingRate()
    {
        return $this->getRate(ActionType::SPENDING);
    }

    /**
     * @param string $type
     *
     * @return Rate|null
     * @throws NoSuchEntityException
     */
    public function getRate($type)
    {
        if ($type === ActionType::EARNING) {
            $rate = $this->helper->getCalculationHelper()->getEarningRate();
        } else {
            $rate = $this->helper->getCalculationHelper()->getSpendingRate();
        }

        if (!$rate->isValid()) {
            return null;
        }

        return $rate;
    }
}
