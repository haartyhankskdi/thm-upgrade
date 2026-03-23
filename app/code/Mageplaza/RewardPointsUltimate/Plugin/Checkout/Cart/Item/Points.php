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

namespace Mageplaza\RewardPointsUltimate\Plugin\Checkout\Cart\Item;

use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;
use Psr\Log\LoggerInterface;

/**
 * Class Points
 * @package Mageplaza\RewardPointsUltimate\Plugin\Checkout\Cart\Item
 */
class Points
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SellPoint
     */
    protected $sellPoint;

    /**
     * Points constructor.
     *
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     * @param SellPoint $sellPoint
     */
    public function __construct(HelperData $helperData, LoggerInterface $logger, SellPoint $sellPoint)
    {
        $this->helperData = $helperData;
        $this->logger     = $logger;
        $this->sellPoint  = $sellPoint;
    }

    /**
     * @param Renderer $subject
     * @param string $result
     *
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function afterGetUnitPriceHtml(Renderer $subject, $result)
    {
        return $this->getRewardSellProduct($result, $subject, false);
    }

    /**
     * @param Renderer $subject
     * @param string $result
     *
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function afterGetRowTotalHtml(Renderer $subject, $result)
    {
        return $this->getRewardSellProduct($result, $subject, true);
    }

    /**
     * @param string $result
     * @param Renderer $subject
     * @param bool $isQty
     *
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getRewardSellProduct($result, $subject, $isQty)
    {
        $qty                     = $isQty ? $subject->getItem()->getQty() : 1;
        $mpRewardSellProductItem = $subject->getItem()->getMpRewardSellPoints();
        $mpRewardSellProduct     = $this->sellPoint->getRewardSellProductBySku(
            $subject->getItem()->getSku(),
            $qty
        );
        if ($mpRewardSellProduct && $mpRewardSellProductItem > 0) {
            return $this->getFormatHtml($this->helperData->getPointHelper()->format($mpRewardSellProduct, false));
        }

        return $result;
    }

    /**
     * @param string $mpRewardSellProduct
     *
     * @return string
     */
    public function getFormatHtml($mpRewardSellProduct)
    {
        return '<span class="price-excluding-tax">
                    <span class="cart-price">
                        <span class="price">' . $mpRewardSellProduct . '</span>
                    </span>
            </span>';
    }
}
