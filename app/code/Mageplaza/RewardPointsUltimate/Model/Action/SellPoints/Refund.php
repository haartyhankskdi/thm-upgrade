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

namespace Mageplaza\RewardPointsUltimate\Model\Action\SellPoints;

use Mageplaza\RewardPoints\Model\Action\Spending;

/**
 * Class Refund
 * @package Mageplaza\RewardPointsUltimate\Model\Action\SellPoints
 */
class Refund extends Spending
{
    const CODE = 'sell_points_order_refund';

    /**
     * @inheritdoc
     */
    public function getActionLabel()
    {
        return __('Sell points refund');
    }

    /**
     * @inheritdoc
     */
    public function getTitle($transaction)
    {
        return $this->getComment($transaction, 'Taken back points from purchased products by points on order #%1');
    }
}
