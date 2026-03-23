<?php
namespace Haartyhanks\BestSellerApi\Model;

use Haartyhanks\BestSellerApi\Api\BestSellerInterface;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\Catalog\Model\Product\Type;

class BestSeller implements BestSellerInterface
{
    protected $collectionFactory;
    protected $productRepository;
    protected $storeManager;
    protected $isProductSalable;

    public function __construct(
        CollectionFactory $collectionFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        IsProductSalableInterface $isProductSalable
    ) {
        $this->collectionFactory  = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->storeManager      = $storeManager;
        $this->isProductSalable  = $isProductSalable;
    }

   public function getBestSellers($limit = 15)
{
    $collection = $this->collectionFactory->create();
    $collection->setPeriod('month')->setPageSize($limit);

    $store   = $this->storeManager->getStore();
    $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    $baseUrl  = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);

    $result = [];
    $seenProducts = [];

    foreach ($collection as $item) {
        try {
            $product = $this->productRepository->getById(
                $item->getProductId(),
                false,
                $store->getId()
            );

            $productId = (int)$product->getId();
            if (isset($seenProducts[$productId])) {
                continue;
            }
            $seenProducts[$productId] = true;

            $image = $product->getImage();
            $imageUrl = ($image && $image !== 'no_selection')
                ? $mediaUrl . 'catalog/product' . $image
                : '';

            $isInStock = $this->isProductSalable->execute(
                $product->getSku(),
                1
            );
            $stockStatus = $isInStock ? 'IN_STOCK' : 'OUT_OF_STOCK';

            $customOptions = [];
            if ($product->getTypeId() === Type::TYPE_SIMPLE) {
                foreach ($product->getOptions() as $option) {
                    $values = [];
                    if ($option->getValues()) {
                        foreach ($option->getValues() as $value) {
                            $values[] = [
                                'option_type_id' => (int)$value->getOptionTypeId(),
                                'title' => $value->getTitle(),
                                'price' => (float)$value->getPrice(),
                                'price_type' => $value->getPriceType()
                            ];
                        }
                    }

                    $customOptions[] = [
                        'option_id' => (int)$option->getOptionId(),
                        'title' => $option->getTitle(),
                        'type' => $option->getType(),
                        'required' => (bool)$option->getIsRequire(),
                        'values' => $values
                    ];
                }
            }

            $productUrl = $product->getUrlKey()
                ? $baseUrl . $product->getUrlKey() . '.html'
                : '';

            $result[] = [
                'id' => $productId,
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'qty_ordered' => (float)$item->getQtyOrdered(),
                'image' => $imageUrl,
                'price' => (float)$product->getPrice(),
                'special_price' => $product->getSpecialPrice()
                    ? (float)$product->getSpecialPrice()
                    : null,
                    'weight' => $product->getWeight(),
                'ingredients' => $product->getIngredients(),
                'storage_guidelines' => $product->getStorageGuidelines(),
                'cooking_suggestions' => $product->getCookingSuggestions(),
                'stock_status' => $stockStatus,
                'short_description' => $product->getShortDescription(),
                'description' => $product->getDescription(),
                'options' => $customOptions,
                'product_url' => $productUrl
            ];

        } catch (\Exception $e) {
            continue;
        }
    }

    return $result;
}

}
