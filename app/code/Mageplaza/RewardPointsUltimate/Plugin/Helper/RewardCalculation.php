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

namespace Mageplaza\RewardPointsUltimate\Plugin\Helper;

use Magento\Framework\App\RequestInterface;
use Mageplaza\RewardPointsPro\Plugin\Helper\Calculation;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class RewardCalculation
 * @package Mageplaza\RewardPointsUltimate\Plugin\Helper
 */
class RewardCalculation
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * RewardCalculation constructor.
     *
     * @param Data $helperData
     * @param RequestInterface $request
     */
    public function __construct(
        Data $helperData,
        RequestInterface $request
    ) {
        $this->helperData = $helperData;
        $this->request    = $request;
    }

    /**
     * @param Calculation $subject
     * @param array $spendingConfig
     *
     * @return mixed
     */
    public function afterAroundGetSpendingConfiguration(Calculation $subject, $spendingConfig)
    {
        if ($this->request->getFullActionName() !== 'sales_order_create_index') {
            $quote                    = $this->helperData->getQuote();
            $storeId                  = $quote->getStoreId();
            $spendingPointsWithCoupon = $this->helperData->getConfigSpending('spending_point_with_coupon', $storeId);

            if ($quote->getCouponCode() && !$spendingPointsWithCoupon) {
                $spendingConfig['rules'] = [];
            }
        }

        return $spendingConfig;
    }
}
