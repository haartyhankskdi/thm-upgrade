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

namespace Mageplaza\RewardPointsUltimate\Model\Action\Earning;

use Mageplaza\RewardPoints\Model\Action\Earning;

/**
 * Class LifetimeAmount
 * @package Mageplaza\RewardPointsUltimate\Model\Action\Earning
 */
class LifetimeAmount extends Earning
{
    const CODE = 'lifetime_amount';

    /**
     * @inheritdoc
     */
    public function getActionLabel()
    {
        return __('Lifetime Amount');
    }

    /**
     * @inheritdoc
     */
    public function getTitle($transaction)
    {
        return __('Earn point(s) for Lifetime Amount');
    }

}
