<?php

namespace Haartyhanks\UpsellApi\Model;

use Haartyhanks\UpsellApi\Api\RelatedProductsBySkuInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Type;

class RelatedProductsBySku implements RelatedProductsBySkuInterface
{
    protected ProductRepositoryInterface $productRepository;
    protected CategoryRepositoryInterface $categoryRepository;
    protected Image $imageHelper;
    protected GetProductSalableQtyInterface $salableQty;
    protected StoreManagerInterface $storeManager;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        Image $imageHelper,
        GetProductSalableQtyInterface $salableQty,
        StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->imageHelper = $imageHelper;
        $this->salableQty = $salableQty;
        $this->storeManager = $storeManager;
    }

    public function getRelatedProducts($sku)
    {
        $currentProduct = $this->productRepository->get($sku);
        $categoryIds = $currentProduct->getCategoryIds();

        $childCategoryId = null;
        $parentCategoryId = null;

        foreach ($categoryIds as $catId) {
            $category = $this->categoryRepository->get($catId);
            if ($category->getParentId() > 2) {
                $childCategoryId = $catId;
                break;
            } else {
                $parentCategoryId = $catId;
            }
        }

        $finalCategoryId = $childCategoryId ?: $parentCategoryId;
        if (!$finalCategoryId) {
            return [];
        }

        $store = $this->storeManager->getStore();
        $currencyCode = $store->getCurrentCurrencyCode();

        $category = $this->categoryRepository->get($finalCategoryId);
        $collection = $category->getProductCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('entity_id', ['neq' => $currentProduct->getId()])
            ->setPageSize(8);

        $products = [];
        
        foreach ($collection as $item) {
        
            $product = $this->productRepository->getById(
                $item->getId(),
                false,
                $store->getId()
            );

            $qty = $this->salableQty->execute($product->getSku(), 1);
            $stockStatus = $qty > 0 ? 'IN_STOCK' : 'OUT_OF_STOCK';

               $mediaUrl = $this->storeManager
    ->getStore()
    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

$image = $product->getSmallImage();

$imageUrl = ($image && $image !== 'no_selection')
    ? $mediaUrl . 'catalog/product' . $image
    : '';
            $options = [];
            if ($product->getTypeId() === Type::TYPE_SIMPLE) {
                foreach ($product->getOptions() as $option) {
                    $values = [];
                    foreach ((array)$option->getValues() as $value) {
                        $values[] = [
                            'option_type_id' => (int)$value->getOptionTypeId(),
                            'title' => $value->getTitle(),
                            'price' => (float)$value->getPrice(),
                            'price_type' => $value->getPriceType()
                        ];
                    }

                    $options[] = [
                        'option_id' => (int)$option->getOptionId(),
                        'title' => $option->getTitle(),
                        'required' => (bool)$option->getIsRequire(),
                        'value' => $values
                    ];
                }
            }

            $products[] = [
                'id' => (int)$product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'stock_status' => $stockStatus,

                'ingredients' => $product->getIngredients(),
                'storage_guidelines' => $product->getStorageGuidelines(),
                'cooking_suggestions' => $product->getCookingSuggestions(),

                'short_description' => [
                    'html' => $product->getShortDescription() ?? ''
                ],
                'description' => [
                    'html' => $product->getDescription() ?? ''
                ],

                'price_range' => [
                    'minimum_price' => [
                        'final_price' => [
                            'value' => (float)$product->getFinalPrice(),
                            'currency' => $currencyCode
                        ]
                    ]
                ],

                'thumbnail' => [
                    'url' => $imageUrl
                ],

                'weight' => $product->getWeight(),
                'options' => $options
            ];
        }

        return [
            'items' => $products
        ];
    }
}
