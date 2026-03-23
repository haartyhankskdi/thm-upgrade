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

namespace Mageplaza\RewardPointsUltimate\Model;

use Mageplaza\RewardPoints\Model\Account as StandardAccount;

/**
 * Class Account
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class Account extends StandardAccount
{
    /**
     * @param string $actionCode
     * @param string $period
     *
     * @return string
     */
    public function getMilestoneTotalEarningPoints($actionCode, $period)
    {
        return $this->getResource()->getMilestoneTotalPoints($this, $actionCode, $period) ?: 0;
    }

    /**
     * @return int
     */
    public function getTotalOrder()
    {
        return $this->getResource()->getTotalOrder($this->getCustomerId());
    }
}
