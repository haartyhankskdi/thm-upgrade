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

namespace Mageplaza\RewardPointsUltimate\Plugin\Block\Adminhtml\Order\Create;

use Mageplaza\RewardPoints\Block\Adminhtml\Order\Create\SpendingPoints;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class SpendingPointsWithCoupon
 * @package Mageplaza\RewardPointsUltimate\Plugin\Block\Adminhtml\Order\Create
 */
class SpendingPointsWithCoupon
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * SpendingPointsWithCoupon constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param SpendingPoints $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetRewardSpendingConfig(SpendingPoints $subject, $result)
    {
        $storeId                                        = $subject->getStoreId();
        $result['spending']['spendingPointsWithCoupon'] = $this->helperData->getConfigSpending(
            'spending_point_with_coupon',
            $storeId
        );

        return $result;
    }
}
