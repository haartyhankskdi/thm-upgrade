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

namespace Mageplaza\RewardPointsUltimate\Plugin\Order\View;

use Magento\Sales\Model\Order;

/**
 * Class CanCreditmemo
 * @package Mageplaza\RewardPointsUltimate\Plugin\Order\View
 */
class CanCreditmemo
{
    /**
     * @param Order $subject
     */
    public function beforeCanCreditmemo(Order $subject)
    {
        foreach ($subject->getItems() as $item) {
            if ($item->getMpRewardSellPoints() > 0 && ($item->getQtyRefunded() < $item->getQtyInvoiced())) {
                $subject->setForcedCanCreditmemo(true);
                break;
            }
        }
    }
}
