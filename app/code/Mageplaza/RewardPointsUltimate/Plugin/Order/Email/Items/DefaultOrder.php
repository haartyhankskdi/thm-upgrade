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

namespace Mageplaza\RewardPointsUltimate\Plugin\Order\Email\Items;

use Mageplaza\RewardPointsUltimate\Helper\SellPoint;

/**
 * Class DefaultOrder
 * @package Mageplaza\RewardPointsUltimate\Plugin\Order\Email\Items
 */
class DefaultOrder
{
    /**
     * @var SellPoint
     */
    protected $sellPoint;

    /**
     * DefaultOrder constructor.
     *
     * @param SellPoint $sellPoint
     */
    public function __construct(SellPoint $sellPoint)
    {
        $this->sellPoint = $sellPoint;
    }

    /**
     * @param \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder $subject
     * @param callable $proceed
     * @param $item
     *
     * @return bool|string
     */
    public function aroundGetItemPrice(
        \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder $subject,
        callable $proceed,
        $item
    ) {
        $result = $this->sellPoint->getMpRewardSellPoints($subject->getItem());
        if ($result) {
            return $result;
        }

        return $proceed($item);
    }
}
