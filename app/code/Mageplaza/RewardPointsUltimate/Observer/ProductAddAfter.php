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

namespace Mageplaza\RewardPointsUltimate\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;

/**
 * Class ProductAddAfter
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class ProductAddAfter implements ObserverInterface
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
     * @var RequestInterface
     */
    protected $request;

    /**
     * ProductAddAfter constructor.
     *
     * @param RequestInterface $request
     * @param HelperData $helperData
     * @param SellPoint $sellPoint
     */
    public function __construct(
        RequestInterface $request,
        HelperData $helperData,
        SellPoint $sellPoint
    ) {
        $this->helperData = $helperData;
        $this->sellPoint  = $sellPoint;
        $this->request    = $request;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->helperData->isEnabled()) {
            $item    = $observer->getEvent()->getData('quote_item');
            $item    = $item->getParentItem() ?: $item;
            $product = $item->getProduct()->load($item->getProductId());
            $price   = $product->getFinalPrice();

            $optionPrice = 0;
            foreach ($product->getOptions() as $option) {
                $optionPrice += (int) $option['default_price'];
            }

            if ($product->getData('tier_price') && ($tierPrice = $product->getTierPrice($item->getQty()))) {
                $childId = array_keys($item->getQtyOptions());
                if ($product->getTypeId() === 'configurable') {
                    $children = $product->getTypeInstance()->getUsedProducts($product);
                    foreach ($children as $child) {
                        if ($child->getId() == $childId[0]) {
                            $tierPrice = $child->getTierPrice($item->getQty());
                        }
                    }
                }
                if ($tierPrice < $price) {
                    $price = $tierPrice;
                    $item->setCustomPrice($price);
                    $item->setOriginalCustomPrice($price);
                    $item->setBaseOriginalPrice($price);
                }
            }

            $item->setMpRewardSellPoints(null);

            if ((int) $this->request->getParam('mp_sell_product_by')) {
                $mpSellProduct = $product->getData('mp_reward_sell_product');
                if ($product->getTypeId() === 'configurable'
                    && $this->sellPoint->getRewardSellProductBySku($item->getSku())) {
                    $mpSellProduct = $this->sellPoint->getRewardSellProductBySku($item->getSku());
                }
                if ($mpSellProduct > 0) {
                    $price = 0;
                    $item->setCustomPrice($price);
                    $item->setOriginalCustomPrice($price);
                    $item->setBaseOriginalPrice($price);
                    $item->setMpRewardSellPoints($mpSellProduct);
                    $item->getProduct()->setIsSuperMode(true);
                }
            }
        }
    }
}
