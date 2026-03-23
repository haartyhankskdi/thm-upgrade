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

use Mageplaza\RewardPoints\Helper\Calculation;

/**
 * Class SpentWithSellPoints
 * @package Mageplaza\RewardPointsUltimate\Plugin\Helper
 */
class SpentWithSellPoints
{
    /**
     * @var Calculation
     */
    private $helper;

    public function __construct(
        Calculation $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param Calculation $subject
     * @param $result
     * @param $quote
     * @return float|int
     */
    public function afterGetMaxSpendingPointsByRate(Calculation $subject, $result, $quote)
    {
        $pointBalance = $this->helper->getAccountHelper()->getByCustomerId($quote->getCustomerId())->getPointBalance();
        $sellPoints   = 0;
        foreach ($quote->getAllItems() as $item) {
            $sellPoints += $item->getMpRewardSellPoints() * $item->getQty();
        }
        if ($sellPoints && ($pointBalance - $sellPoints) < $result) {
            $result = $pointBalance - $sellPoints;
        }

        return $result;
    }
}
