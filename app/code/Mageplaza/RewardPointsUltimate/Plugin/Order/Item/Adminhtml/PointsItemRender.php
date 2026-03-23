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

namespace Mageplaza\RewardPointsUltimate\Plugin\Order\Item\Adminhtml;

use Magento\Sales\Block\Adminhtml\Items\AbstractItems;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;

/**
 * Class PointsItemRender
 * @package Mageplaza\RewardPointsUltimate\Plugin\Order\Item\Adminhtml
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
     * @param AbstractItems $subject
     * @param callable $proceed
     * @param $item
     * @param $column
     * @param null $field
     *
     * @return bool|string
     */
    public function aroundGetColumnHtml(AbstractItems $subject, callable $proceed, $item, $column, $field = null)
    {
        $result = '';
        $item = $subject->getItem();
        if ($column == 'price') {
            $result = $this->sellPoint->getMpRewardSellPoints($item);
        } elseif ($column == 'total' || $column == 'subtotal') {
            $result = $this->sellPoint->getMpRewardSellPoints($item, true);
        }
        if ($result) {
            return $result;
        }

        return $proceed($item, $column, $field);
    }
}
