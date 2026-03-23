<?php
namespace Haartyhanks\GreatBritishApi\Model;

use Haartyhanks\GreatBritishApi\Api\GreatBritishInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\CategoryFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class GreatBritish implements GreatBritishInterface
{
    protected $productCollectionFactory;
    protected $categoryFactory;
    protected $visibility;
    protected $stockRegistry;
    protected $storeManager;
    protected $productRepository;

    public function __construct(
        CollectionFactory $productCollectionFactory,
        CategoryFactory $categoryFactory,
        Visibility $visibility,
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->visibility = $visibility;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    public function getGreatBritish($categoryId = null)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect([
                'name',
                'price',
                'special_price',
                'weight',
                'small_image',
                'short_description',
                'description',
                'ingredients',
                'storage_guidelines',
                'cooking_suggestions',
                'created_at',
                'has_options'
            ])
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter(
                'visibility',
                ['in' => $this->visibility->getVisibleInCatalogIds()]
            )
            ->setOrder('created_at', 'desc')
            ->setPageSize(12);

        if ($categoryId) {
            $category = $this->categoryFactory->create()->load($categoryId);
            $childIds = $category->getAllChildren(true);
            $collection->addCategoriesFilter(['in' => $childIds]);
        }

        $mediaBaseUrl = $this->storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        $result = [];

        foreach ($collection as $product) {

            $imagePath = $product->getSmallImage();
            $imageUrl = $mediaBaseUrl . 'catalog/product/placeholder/default/no-image_4.jpg';

            if ($imagePath && $imagePath !== 'no_selection') {
                $imageUrl = $mediaBaseUrl . 'catalog/product' . $imagePath;
            }
            $productUrl = $product->getProductUrl();

            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            $stockStatus = $stockItem->getIsInStock() ? 'In Stock' : 'Out of Stock';

            $customOptions = [];

            if ($product->getHasOptions()) {

                $fullProduct = $this->productRepository->getById(
                    $product->getId(),
                    false,
                    $this->storeManager->getStore()->getId()
                );

                if ($fullProduct->getOptions()) {
                    foreach ($fullProduct->getOptions() as $option) {
                        $values = [];
                        foreach ((array) $option->getValues() as $value) {
                            $values[] = [
                                'option_type_id' => $value->getOptionTypeId(),
                                'title' => $value->getTitle(),
                                'price' => (float) $value->getPrice(),
                                'price_type' => $value->getPriceType()
                            ];
                        }

                        $customOptions[] = [
                            'option_id' => $option->getOptionId(),
                            'title' => $option->getTitle(),
                            'type' => $option->getType(),
                            'is_required' => (bool) $option->getIsRequire(),
                            'values' => $values
                        ];
                    }
                }
            }

            $result[] = [
                'id' => $product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'image' => $imageUrl,
                'price' => (float) $product->getPrice(),
                'special_price' => $product->getSpecialPrice()
                    ? (float) $product->getSpecialPrice()
                    : null,
                'weight' => (float) $product->getWeight(),
                'ingredients' => $product->getData('ingredients'),
                'storage_guidelines' => $product->getData('storage_guidelines'),
                'cooking_suggestions' => $product->getData('cooking_suggestions'),
                'stock_status' => $stockStatus,
                'short_description' => $product->getShortDescription(),
                'description' => $product->getDescription(),
                'options' => $customOptions,
                'product_url' => $productUrl,
                'created_at' => $product->getCreatedAt()
            ];
        }

        return $result;
    }
}
