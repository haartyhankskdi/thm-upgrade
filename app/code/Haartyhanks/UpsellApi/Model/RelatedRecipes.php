<?php

namespace Haartyhanks\UpsellApi\Model;

use Haartyhanks\UpsellApi\Api\RelatedRecipesInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

class RelatedRecipes implements RelatedRecipesInterface
{
    protected ProductRepositoryInterface $productRepository;
    protected PostCollectionFactory $postCollectionFactory;
    protected Registry $registry;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        PostCollectionFactory $postCollectionFactory,
        Registry $registry
    ) {
        $this->productRepository = $productRepository;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->registry = $registry;
    }

    public function getRelatedRecipes($productId)
    {
        $product = $this->productRepository->getById($productId);
        $productCategoryIds = $product->getCategoryIds();

        // Current category (same as PHTML)
        $currentCategory = $this->registry->registry('current_category');
        $recipeCatId = $currentCategory
            ? $currentCategory->getData('recipe_cat_id')
            : null;

        $collection = $this->postCollectionFactory->create();
        $collection->addFieldToFilter('enabled', 1)
                   ->setOrder('publish_date', 'DESC');

        $recipes = [];
        if (!empty($productCategoryIds)) {
            foreach ($collection as $post) {
                $post->load($post->getId());
                $postCategoryIds = $post->getCategoryIds();

                if (
                    array_intersect($productCategoryIds, $postCategoryIds)
                    && $post->getShortDescription()
                ) {
                    $recipes[] = $this->formatPost($post);
                }

                if (count($recipes) >= 2) {
                    break;
                }
            }
        }
        if (empty($recipes) && $recipeCatId) {
            foreach ($collection as $post) {
                $post->load($post->getId());

                if (
                    in_array($recipeCatId, $post->getCategoryIds())
                    && $post->getShortDescription()
                ) {
                    $recipes[] = $this->formatPost($post);
                }

                if (count($recipes) >= 2) {
                    break;
                }
            }
        }
        if (empty($recipes)) {
            foreach ($collection as $post) {
                if ($post->getShortDescription()) {
                    $recipes[] = $this->formatPost($post);
                }

                if (count($recipes) >= 2) {
                    break;
                }
            }
        }

        return [
            'product_id' => $productId,
            'recipes' => $recipes
        ];
    }

    private function formatPost($post): array
    {
        return [
            'id' => $post->getId(),
            'title' => $post->getName(),
            'url' => $post->getUrl(),
            'short_description' => $post->getShortDescription()
        ];
    }
}
