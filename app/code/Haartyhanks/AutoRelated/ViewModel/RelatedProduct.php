<?php
namespace Haartyhanks\AutoRelated\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;

class RelatedProduct implements ArgumentInterface
{
    protected $checkoutSession;
    protected $productCollectionFactory;
    protected $productRepository;
    protected $imageHelper;

    public function __construct(
        CheckoutSession $checkoutSession,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        Image $imageHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
    }

    public function getRelatedProducts(): array
    {
        $quote = $this->checkoutSession->getQuote();
        $items = $quote->getAllVisibleItems();

        $allRelated = [];
        $cartProductIds = [];

        foreach ($items as $item) {
            $cartProductIds[] = $item->getProductId();
        }

        foreach ($items as $item) {
            $productId = $item->getProductId();
            try {
                $product = $this->productRepository->getById($productId);
            } catch (\Exception $e) {
                continue;
            }

            $categoryIds = $product->getCategoryIds();
            if (empty($categoryIds)) continue;

            $targetCategoryId = $categoryIds[0];

            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect(['name', 'price', 'small_image'])
                ->addCategoriesFilter(['in' => [$targetCategoryId]])
                ->addAttributeToFilter('entity_id', ['nin' => $cartProductIds])
                ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                ->addAttributeToFilter('visibility', ['neq' => 1])
                ->setPageSize(5);

            foreach ($collection as $related) {
                $allRelated[$related->getId()] = [
                    'id'    => $related->getId(),
                    'name'  => $related->getName(),
                    'url'   => $related->getProductUrl(),
                    'image' => $this->imageHelper->init($related, 'product_small_image')->getUrl(),
                    'price' => $related->getFinalPrice()
                ];
            }
        }

        // Limit to 5
        if (count($allRelated) > 5) {
            $keys = array_rand($allRelated, 5);
            $limited = [];
            foreach ((array)$keys as $k) $limited[] = $allRelated[$k];
            return $limited;
        }

        return array_values($allRelated);
    }
}
