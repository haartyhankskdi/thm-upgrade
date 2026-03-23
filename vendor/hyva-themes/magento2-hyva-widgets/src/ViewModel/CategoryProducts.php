<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\Widgets\ViewModel;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/** @SuppressWarnings(PHPMD.LongVariable) */
class CategoryProducts implements ArgumentInterface
{
    public const DEFAULT_LIMIT = 16;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(
        CollectionFactory $productCollectionFactory,
        CategoryRepository $categoryRepository
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryRepository       = $categoryRepository;
    }

    public function getProductCollection(
        $categoryId,
        $sortBy = null,
        $sortDirection = null,
        int $limit = self::DEFAULT_LIMIT
    ): Collection {
        $category = $this->categoryRepository->get($categoryId);
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoryFilter($category);
        $collection->setOrder($sortBy ?? 'position', $sortDirection ?? 'asc');
        $collection->setPageSize($limit);
        $collection->addAttributeToFilter('visibility', Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);

        return $collection;
    }
}
