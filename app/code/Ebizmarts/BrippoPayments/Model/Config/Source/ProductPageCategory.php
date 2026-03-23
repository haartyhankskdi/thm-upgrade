<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Option\ArrayInterface;
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;
use Ebizmarts\BrippoPayments\Helper\Data as HelperData;

class ProductPageCategory implements ArrayInterface
{
    const ALL_CATEGRORIES = 'ALL';
    const PAGE_SIZE = 600;

    protected $_categoryFactory;
    protected $_categoryCollectionFactory;

    /** @var ExpressHelper */
    protected $expressHelper;

    /** @var HelperData */
    protected $helperData;

    /**
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $categoryCollectionFactory
     * @param ExpressHelper $expressHelper
     * @param HelperData $helperData
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CollectionFactory $categoryCollectionFactory,
        ExpressHelper $expressHelper,
        HelperData $helperData
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->expressHelper = $expressHelper;
        $this->helperData = $helperData;
    }

    /**
     * @param $isActive
     * @param $level
     * @param $sortBy
     * @param $pageSize
     * @return Collection
     * @throws LocalizedException
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray()
    {
        $arr = $this->_toArray();
        $ret = [];

        $ret[] = [
            'value' => self::ALL_CATEGRORIES,
            'label' => 'All Categories'
        ];

        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function _toArray()
    {
        // don't query categories when the product page is not enabled
        $scope = $this->helperData->getScopeIdFromUrl();
        if (!$this->expressHelper->isAvailableAtLocation($scope, ExpressLocation::PRODUCT_PAGE)) {
            return [];
        }

        $categories = $this->getCategoryCollection(true, false, false, self::PAGE_SIZE);

        $categoryList = [];
        foreach ($categories as $category) {
            $categoryList[$category->getEntityId()] =
                __($this->_getParentName($category->getPath()) . $category->getName());
        }

        return $categoryList;
    }

    /**
     * @param $path
     * @return string
     */
    private function _getParentName($path = '')
    {
        $parentName = '';
        $rootCats = [1,2];

        $catTree = explode("/", (string)$path);
        // Deleting category itself
        array_pop($catTree);

        if ($catTree && (count($catTree) > count($rootCats))) {
            foreach ($catTree as $catId) {
                if (!in_array($catId, $rootCats)) {
                    $category = $this->_categoryFactory->create()->load($catId);
                    $categoryName = $category->getName();
                    $parentName .= $categoryName . ' / ';
                }
            }
        }

        return $parentName;
    }
}
