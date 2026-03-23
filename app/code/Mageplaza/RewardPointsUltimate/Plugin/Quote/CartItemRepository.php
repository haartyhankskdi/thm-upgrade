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

namespace Mageplaza\RewardPointsUltimate\Plugin\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;
use Magento\Quote\Model\Quote\Item\Repository;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;

/**
 * Class CartItemRepository
 * @package Mageplaza\RewardPoints\Plugin\Quote
 */
class CartItemRepository
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var SellPoint
     */
    protected $sellPoint;

    /**
     * CartItemRepository constructor.
     *
     * @param HelperData $helperData
     * @param ProductRepositoryInterface $productRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param SellPoint $sellPoint
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        HelperData $helperData,
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $quoteRepository,
        SellPoint $sellPoint,
        StoreManagerInterface $storeManager
    ) {
        $this->helperData        = $helperData;
        $this->productRepository = $productRepository;
        $this->quoteRepository   = $quoteRepository;
        $this->sellPoint         = $sellPoint;
        $this->storeManager      = $storeManager;
    }

    /**
     * @param Repository $subject
     * @param $cartItem
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeSave(Repository $subject, $cartItem)
    {
        $storeId = $this->storeManager->getStore()->getId();

        if ($this->helperData->isEnabled($storeId)) {
            /** @var Item $cartItem */
            $cartId = $cartItem->getQuoteId();
            if (!$cartId) {
                throw new NoSuchEntityException(
                    __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'quoteId'])
                );
            }
            $quote      = $this->quoteRepository->getActive($cartId);
            $productSku = $cartItem->getSku();
            if ($productSku) {
                $product = $this->productRepository->get($productSku, false, $storeId);
                if ($product->getMpRewardSellProduct() > 0) {
                    $sellPoints = $product->getMpRewardSellProduct() * $cartItem->getQty();
                    if (!$this->sellPoint->isValid($sellPoints, $quote)) {
                        throw new NoSuchEntityException(__('You haven\'t enough point to add this product!'));
                    }
                }
            }
        }

        return [$cartItem];
    }
}
