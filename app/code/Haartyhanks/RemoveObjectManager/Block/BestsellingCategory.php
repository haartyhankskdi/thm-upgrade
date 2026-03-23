<?php
namespace Haartyhanks\RemoveObjectManager\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Registry;
use Magento\Catalog\Helper\Image;

class BestsellingCategory extends Template
{
    protected $productCollectionFactory;
    protected $categoryRepository;
    protected $visibility;
    protected $registry;
    protected $imageHelper;

    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        CategoryRepository $categoryRepository,
        Visibility $visibility,
        Registry $registry,
        Image $imageHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->visibility = $visibility;
        $this->registry = $registry;
        $this->imageHelper = $imageHelper;
    }

    public function getCurrentCategoryId()
    {
        $category = $this->registry->registry('current_category');
        return $category ? (int)$category->getId() : null;
    }

    public function getImageHelper()
    {
        return $this->imageHelper;
    }

    public function getBestSellingProducts($limit = 4, $categoryId = null)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', ['in' => $this->visibility->getVisibleInCatalogIds()]);

        if ($categoryId) {
            try {
                $this->categoryRepository->get($categoryId); // check if exists
                $collection->addCategoriesFilter(['in' => [$categoryId]]);
            } catch (\Exception $e) {
                // skip category filter if invalid
            }
        }

        $collection->getSelect()
            ->joinLeft(
                ['soi' => $collection->getTable('sales_order_item')],
                'e.entity_id = soi.product_id',
                ['ordered_qty' => 'SUM(soi.qty_ordered)']
            )
            ->where('soi.product_id IS NOT NULL')
            ->having('SUM(soi.qty_ordered) > 0')
            ->group('e.entity_id')
            ->order('ordered_qty DESC')
            ->limit($limit);

        return $collection;
    }
}
