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

namespace Mageplaza\RewardPointsUltimate\Plugin\Order\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;

/**
 * Class ConvertSellPoints
 * @package Mageplaza\RewardPointsUltimate\Plugin\Order\Total\Creditmemo
 */
class ConvertSellPoints
{
    /**
     * @var SellPoint
     */
    protected $sellPoint;

    /**
     * ConvertSellPoints constructor.
     *
     * @param SellPoint $sellPoint
     */
    public function __construct(SellPoint $sellPoint)
    {
        $this->sellPoint = $sellPoint;
    }

    /**
     * @param Creditmemo $subject
     */
    public function beforeCollectTotals(Creditmemo $subject)
    {
        if ($this->sellPoint->setMpRewardSellPoints($subject->getItems())) {
            $subject->setAllowZeroGrandTotal(true);
        }
    }
}
