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

namespace Mageplaza\RewardPointsUltimate\Plugin\Order\Item;

use Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;

/**
 * Class PointsItemRender
 * @package Mageplaza\RewardPointsUltimate\Plugin\Order\Item\PointsItemRender
 */
class PointsItemRender
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var SellPoint
     */
    protected $sellPoint;

    /**
     * PointsItemRender constructor.
     *
     * @param HelperData $helperData
     * @param SellPoint $sellPoint
     */
    public function __construct(
        HelperData $helperData,
        SellPoint $sellPoint
    ) {
        $this->helperData = $helperData;
        $this->sellPoint = $sellPoint;
    }

    /**
     * @param DefaultRenderer $subject
     * @param $result
     *
     * @return mixed|string
     */
    public function afterGetItemPriceHtml(DefaultRenderer $subject, $result)
    {
        return $this->sellPoint->getMpRewardSellPoints($subject->getItem()) ?: $result;
    }

    /**
     * @param DefaultRenderer $subject
     * @param $result
     *
     * @return mixed|string
     */
    public function afterGetItemRowTotalHtml(DefaultRenderer $subject, $result)
    {
        return $this->sellPoint->getMpRewardSellPoints($subject->getItem(), true) ?: $result;
    }
}
