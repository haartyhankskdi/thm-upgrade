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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ShippingAssignment;
use Mageplaza\RewardPointsUltimate\Helper\Calculation;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class SpendingPointsWithCoupon
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class SpendingPointsWithCoupon implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Calculation
     */
    protected $calculation;

    /**
     * SpendingPointsWithCoupon constructor.
     *
     * @param Calculation $calculation
     * @param Data $helperData
     */
    public function __construct(
        Calculation $calculation,
        Data $helperData
    ) {
        $this->helperData  = $helperData;
        $this->calculation = $calculation;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        /** @var ShippingAssignment $shippingAssignment */
        $shippingAssignment       = $observer->getEvent()->getShippingAssignment();
        $storeId                  = $quote->getStoreId();
        $spendingPointsWithCoupon = $this->helperData->getConfigSpending('spending_point_with_coupon', $storeId);

        if ($quote->getCouponCode() && !$spendingPointsWithCoupon) {
            $items = $shippingAssignment->getItems();
            $quote->setMpRewardSpent(0);
            $this->calculation->resetRewardData(
                $items,
                $quote,
                [
                    'mp_reward_base_discount',
                    'mp_reward_discount',
                    'mp_reward_shipping_base_discount',
                    'mp_reward_shipping_discount'
                ],
                ['mp_reward_base_discount', 'mp_reward_discount', 'mp_reward_spent']
            );

            return $this;
        }
    }
}
