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
 * Class Order
 * @package Mageplaza\RewardPointsUltimate\Model\Action\SellPoints
 */
class Order extends Spending
{
    const CODE = 'sell_points_order';

    /**
     * @inheritdoc
     */
    public function getActionLabel()
    {
        return __('Purchase products by using points');
    }

    /**
     * @inheritdoc
     */
    public function getTitle($transaction)
    {
        return $this->getComment($transaction, 'Purchase products by using points for order #%1');
    }
}
