<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Search;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\FilterSetting\IsMultiselect;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Amasty\Shopby\Helper\FilterSetting;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;

class RequestGenerator extends \Magento\CatalogSearch\Model\Search\RequestGenerator
{
    public const FAKE_SUFFIX = '_amshopby_filter_';

    /**
     * @var FilterSetting
     */
    private FilterSetting $settingHelper;

    /**
     * @var IsMultiselect
     */
    private IsMultiselect $isMultiselect;

    public function __construct(
        CollectionFactory $productAttributeCollectionFactory,
        FilterSetting $settingHelper,
        IsMultiselect $isMultiselect
    ) {
        $this->settingHelper = $settingHelper;
        parent::__construct($productAttributeCollectionFactory);
        $this->isMultiselect = $isMultiselect;
    }

    /**
     * @return array
     */
    public function generate()
    {
        $requests = [];
        $requests['catalog_view_container'] = $this->generateFakeRequest(
            EavAttributeInterface::IS_FILTERABLE,
            'catalog_view_container'
        );
        $requests['quick_search_container'] = $this->generateFakeRequest(
            EavAttributeInterface::IS_FILTERABLE_IN_SEARCH,
            'quick_search_container'
        );
        return $requests;
    }

    /**
     * @param $attributeType
     * @param $container
     * @return array
     */
    private function generateFakeRequest($attributeType, $container)
    {
        $request = [];
        foreach ($this->getSearchableAttributes() as $attribute) {
            $filterSetting = $this->settingHelper->getSettingByAttribute($attribute);
            if ($attribute->getData($attributeType) && $this->isUseAndLogic($filterSetting)) {
                $request = $this->populateFilterOptions(
                    $request,
                    $container,
                    $attribute->getAttributeCode(),
                    array_keys($attribute->getOptions())
                );
            }
            if ($this->isMultiselectPriceFilter($filterSetting, $attribute)) {
                $request = $this->populateFilterOptions(
                    $request,
                    $container,
                    $attribute->getAttributeCode(),
                    range(0, 15),
                    true
                );
            }
        }

        return $request;
    }

    private function populateFilterOptions(
        array $request,
        string $container,
        string $attributeCode,
        array $keys,
        bool $isRangeFilter = false
    ): array {
        $originalAttributeCode = $attributeCode;

        foreach ($keys as $key) {
            if ($key == 0) {
                continue;
            }

            $attributeCode = $originalAttributeCode . self::FAKE_SUFFIX . $key;
            $queryName = $attributeCode . '_query';

            $request['queries'][$container]['queryReference'][] = [
                'clause' => 'should',
                'ref' => $queryName,
            ];
            $filterName = $attributeCode . self::FILTER_SUFFIX;
            $request['queries'][$queryName] = [
                'name' => $queryName,
                'type' => QueryInterface::TYPE_FILTER,
                'filterReference' => [['ref' => $filterName]],
            ];

            if ($isRangeFilter) {
                $request['filters'][$filterName] = [
                    'type' => FilterInterface::TYPE_RANGE,
                    'name' => $filterName,
                    'field' => $attributeCode,
                    'from' => '$' . $attributeCode . '.from$',
                    'to' => '$' . $attributeCode . '.to$',
                ];
            } else {
                $request['filters'][$filterName] = [
                    'type' => FilterInterface::TYPE_TERM,
                    'name' => $filterName,
                    'field' => $attributeCode,
                    'value' => '$' . $attributeCode . '$',
                ];
            }
        }

        return $request;
    }

    private function isUseAndLogic(FilterSettingInterface $filterSetting): bool
    {
        $isMultiselect = $this->isMultiselect->execute(
            $filterSetting->getAttributeCode(),
            $filterSetting->isMultiselect(),
            $filterSetting->getDisplayMode()
        );

        return $filterSetting->isUseAndLogic() && $isMultiselect;
    }

    private function isMultiselectPriceFilter(FilterSettingInterface $filterSetting, Attribute $attribute): bool
    {
        return $attribute->getFrontendInput() === 'price' && $this->isMultiselect->execute(
            $filterSetting->getAttributeCode(),
            $filterSetting->isMultiselect(),
            $filterSetting->getDisplayMode()
        );
    }
}
