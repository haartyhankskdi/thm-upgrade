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

namespace Mageplaza\RewardPointsUltimate\Helper;

use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order\Item;

/**
 * Class SellPoint
 * @package Mageplaza\RewardPointsUltimate\Helper
 */
class SellPoint extends AbstractHelper
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * SellPoint constructor.
     *
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        Data $helperData
    ) {
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;

        parent::__construct($context);
    }

    /**
     * @param $productId
     * @param int $qty
     *
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getRewardSellProductById($productId, $qty = 1)
    {
        if ($productId) {
            $product = $this->productRepository->getById($productId);
            $mpPoint = $product->getMpRewardSellProduct();
            if ($mpPoint > 0) {
                return $mpPoint * $qty;
            }
        }

        return 0;
    }

    /**
     * @param $productSku
     * @param int $qty
     *
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getRewardSellProductBySku($productSku, $qty = 1)
    {
        $mpPoint = 0;
        try {
            if ($productSku) {
                $product = $this->productRepository->get($productSku);
                $mpPoint = $product->getMpRewardSellProduct();
                if ($mpPoint > 0) {
                    return $mpPoint * $qty;
                }
            }
        } catch (Exception $e) {
            $mpPoint = 0;
        }

        return $mpPoint;
    }

    /**
     * @param int $sellPoints
     * @param Quote| string $quote
     *
     * @return bool
     */
    public function isValid($sellPoints = 0, $quote = '')
    {
        $accountHelper = $this->helperData->getAccountHelper();
        if (!$quote) {
            $quote = $this->helperData->getQuote();
        }

        $rewardCustomer = $accountHelper->getByCustomerId($quote->getCustomerId());
        // $rewardCustomer = $customerId ? $accountHelper->getByCustomerId($customerId) : $accountHelper->get();
        if ($rewardCustomer->getPointBalance() > 0) {
            $sellPoints += $quote->getMpSpent();
            foreach ($quote->getAllItems() as $item) {
                if ($item->getMpRewardSellPoints() > 0) {
                    $sellPoints += ($item->getMpRewardSellPoints() * $item->getQty());
                }
            }
        }

        return !($sellPoints > 0 && $rewardCustomer->getPointBalance() < $sellPoints);
    }

    /**
     * @param $item
     * @param bool $isCalculateQty
     *
     * @return bool|string
     */
    public function getMpRewardSellPoints($item, $isCalculateQty = false)
    {
        $itemPoint = $item->getMpRewardSellPoints();
        if ($itemPoint <= 0) {
            return false;
        }
        $qty = 1;
        if ($isCalculateQty) {
            if ($item instanceof Item) {
                $qty = $item->getQtyOrdered();
            } else {
                $qty = $item->getQty();
            }
        }
        if ($item->getTypeId() === 'configurable' && $this->getRewardSellProductBySku($item->getSku())) {
            $itemPoint = $this->getRewardSellProductBySku($item->getSku());
        }
        $mpRewardSellPoints = $this->helperData->getPointHelper()->format(($itemPoint * $qty), false);

        return '<span class="price-excluding-tax">
                    <span class="cart-price">
                        <span class="price">' . $mpRewardSellPoints . '</span>
                    </span>
                </span>';
    }

    /**
     * @param $items
     *
     * @return mixed
     */
    public function setMpRewardSellPoints($items)
    {
        $mpRewardSellPoints = false;
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $orderItem = $item->getOrderItem();
            $pointOrderItem = $orderItem->getMpRewardSellPoints();
            if ($pointOrderItem > 0) {
                $mpRewardSellPoints = true;
                $item->setMpRewardSellPoints($pointOrderItem);
            }
        }

        return $mpRewardSellPoints;
    }
}
