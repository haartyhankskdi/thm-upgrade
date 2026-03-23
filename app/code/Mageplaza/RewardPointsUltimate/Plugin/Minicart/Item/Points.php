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

namespace Mageplaza\RewardPointsUltimate\Plugin\Minicart\Item;

use Magento\Checkout\CustomerData\Cart;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\ItemFactory;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;
use Psr\Log\LoggerInterface;

/**
 * Class Points
 * @package Mageplaza\RewardPointsUltimate\Plugin\Minicart\Item
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
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * Points constructor.
     *
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     * @param SellPoint $sellPoint
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        HelperData $helperData,
        LoggerInterface $logger,
        SellPoint $sellPoint,
        ItemFactory $itemFactory
    ) {
        $this->helperData  = $helperData;
        $this->logger      = $logger;
        $this->sellPoint   = $sellPoint;
        $this->itemFactory = $itemFactory;
    }

    /**
     * @param Cart $subject
     * @param array $result
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterGetSectionData(Cart $subject, $result)
    {
        if ($this->helperData->isEnabled()) {
            $items       = $result['items'];
            $pointHelper = $this->helperData->getPointHelper();
            foreach ($items as $key => $item) {
                $currentItem = $this->itemFactory->create()->load($item['item_id']);
                if (isset($item['product_id']) && (int) $currentItem->getMpRewardSellPoints() > 0) {
                    $mpRewardSellProduct = $this->sellPoint->getRewardSellProductById($item['product_id']);
                    if ($item['product_type'] === 'configurable'
                        && $this->sellPoint->getRewardSellProductBySku($item['product_sku'])) {
                        $mpRewardSellProduct = $this->sellPoint->getRewardSellProductBySku($item['product_sku']);
                    }
                    if ($mpRewardSellProduct) {
                        $html                         = '<span class="minicart-price"><span class="price">'
                            . $pointHelper->format(
                                $mpRewardSellProduct,
                                false
                            ) . '</span></span>';
                        $items[$key]['product_price'] = $html;
                    }
                }
            }
            $result['items'] = $items;
        }

        return $result;
    }
}
