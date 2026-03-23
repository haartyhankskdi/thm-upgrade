<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Base\Model\MagentoVersion;
use Amasty\Shopby\Api\Data\FromToFilterInterface;
use Amasty\Shopby\Model\Layer\Filter\Resolver\Decimal\FilterConfigResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\Decimal\FilterRequestDataResolver as DecimalFilterRequestDataResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\Decimal\FilterSettingResolver as DecimalFilterSettingResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterSettingResolver;
use Amasty\Shopby\Model\Price\GetPrecisionValue;
use Amasty\Shopby\Model\Price\RemoveExtraZeros;
use Amasty\Shopby\Model\Search\RequestGenerator as ShopbyRequestGenerator;
use Amasty\Shopby\Model\Source\PositionLabel;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Price;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Search\Api\SearchInterface;
use Magento\Store\Model\StoreManagerInterface;

class Decimal extends \Magento\CatalogSearch\Model\Layer\Filter\Decimal implements FromToFilterInterface
{
    public const LABEL_RANGE = 0.01;

    /**
     * @var Price
     */
    private $dataProvider;

    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var array
     */
    private $facetedData;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var string
     */
    private $currencySymbol;

    /**
     * @var string
     */
    private $magentoVersion;

    /**
     * @var FilterSettingResolver
     */
    private $filterSettingResolver;

    /**
     * @var FilterRequestDataResolver
     */
    private $filterRequestDataResolver;

    /**
     * @var DecimalFilterSettingResolver
     */
    private $decimalFilterSettingResolver;

    /**
     * @var DecimalFilterRequestDataResolver
     */
    private $decimalRequestDataResolver;

    /**
     * @var FilterConfigResolver
     */
    private $decimalConfigResolver;

    /**
     * @var int
     */
    private $range = 0;

    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    private $currency;

    /**
     * @var GetPrecisionValue
     */
    private $getPrecisionValue;

    /**
     * @var RemoveExtraZeros
     */
    private $removeExtraZeros;

    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        ItemDataBuilder $itemDataBuilder,
        DecimalFactory $filterDecimalFactory,
        PriceCurrencyInterface $priceCurrency,
        PriceFactory $dataProviderFactory,
        SearchInterface $search,
        ManagerInterface $messageManager,
        MagentoVersion $magentoVersion,
        FilterSettingResolver $filterSettingResolver,
        FilterRequestDataResolver $filterRequestDataResolver,
        DecimalFilterSettingResolver $decimalFilterSettingResolver,
        DecimalFilterRequestDataResolver $decimalRequestDataResolver,
        FilterConfigResolver $decimalConfigResolver,
        GetPrecisionValue $getPrecisionValue,
        RemoveExtraZeros $removeExtraZeros,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $filterDecimalFactory,
            $priceCurrency,
            $data
        );
        $this->currencySymbol = $priceCurrency->getCurrencySymbol();
        $this->currency = $priceCurrency->getCurrency();
        $this->dataProvider = $dataProviderFactory->create(['layer' => $layer]);
        $this->messageManager = $messageManager;
        $this->magentoVersion = $magentoVersion->get();
        $this->search = $search;
        $this->filterSettingResolver = $filterSettingResolver;
        $this->filterRequestDataResolver = $filterRequestDataResolver;
        $this->decimalFilterSettingResolver = $decimalFilterSettingResolver;
        $this->decimalRequestDataResolver = $decimalRequestDataResolver;
        $this->decimalConfigResolver = $decimalConfigResolver;
        $this->getPrecisionValue = $getPrecisionValue;
        $this->removeExtraZeros = $removeExtraZeros;
    }

    /**
     * @param RequestInterface $request
     * @return $this
     * @throws LocalizedException
     */
    public function apply(RequestInterface $request)
    {
        if ($this->filterRequestDataResolver->isApplied($this)) {
            return $this;
        }

        $filterValue = $this->filterRequestDataResolver->getFilterParam($this);

        if (empty($filterValue) || !is_string($filterValue)) {
            return $this;
        }

        $this->filterRequestDataResolver->setCurrentValue($this, null);

        $filters = explode(',', $filterValue);
        $currentKeyForApply = 0;
        foreach ($filters as $filter) {
            $filterValue = $this->getFromToValues($filter);
            $filterParams = explode(',', $filterValue);
            $validateFilter = $this->decimalRequestDataResolver->getValidFilterValue($filterParams[0]);

            if (!$validateFilter) {
                return $this;
            } else {
                $this->decimalRequestDataResolver->addFromTo(
                    $this,
                    (float)$validateFilter[0],
                    (float)$validateFilter[1]
                );
            }

            $this->applyFilter(
                $validateFilter,
                explode('-', $filter),
                (bool)$request->getParam('price-ranges'),
                $currentKeyForApply++
            );
        }

        return $this;
    }

    public function applyFilter(
        array $validatedFilter,
        array $filterValueForState,
        bool $isPriceRangesUsed = false,
        int $key = 0
    ): void {
        [$from, $to] = $validatedFilter;

        $this->getLayer()->getProductCollection()->addFieldToFilter(
            $this->getFakeAttributeCodeForApply($key),
            ['from' => $from, 'to' => $isPriceRangesUsed && $to ? $to - self::LABEL_RANGE : $to]
        );

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->renderRangeLabel(empty($from) ? 0 : $from, $to), $filterValueForState)
        );
    }

    /**
     * @param string $filter
     * @return string
     */
    private function getFromToValues($filter): string
    {
        if (strpos($filter, '-') === false) {
            return '';
        }

        [$from, $to] = explode('-', $filter);
        $from = $from ?: 0;
        $to = $to ?: 0;

        return sprintf('%s-%s', (float) $from, (float) $to);
    }

    /**
     * @return array
     */
    public function getFromToConfig(): array
    {
        return $this->decimalConfigResolver->getConfig($this, $this->getFacetedData());
    }

    /**
     * @return array
     */
    protected function _getItemsData()
    {
        if ($this->filterRequestDataResolver->isHidden($this)) {
            return [];
        }

        $facets = $this->getFacetedData();

        $data = [];
        foreach ($facets as $key => $aggregation) {
            if ($key === 'data') {
                continue;
            }

            [$from, $to] = $this->prepareFromToItemData($key);

            if (!$this->range) {
                if (in_array($from, [0, '*']) && $to != '') {
                    $this->range = $to;
                } elseif ($to != '') {
                    $this->range = $to - $from;
                }
            }

            if ($to == '') {
                $to = $this->range ? $from + $this->range : ($facets['data']['max'] ?? 0);
            }

            $label = $this->renderRangeLabel(
                empty($from) ? 0 : $from,
                $to
            );

            $filterSetting = $this->filterSettingResolver->getFilterSetting($this);
            $format = $this->getFormat($filterSetting, $from, (float)$to);
            $value = sprintf($format, $from, $to);
            $from = $this->removeExtraZeros->execute($filterSetting, $from);
            $to = $this->removeExtraZeros->execute($filterSetting, (float)$to);
            $data[] = [
                'label' => $label,
                'value' =>  $value,
                'count' => $aggregation['count'],
                'from' => $from,
                'to' => $to
            ];
        }

        return $data;
    }

    private function getFormat(FilterSettingInterface $filterSetting, float $from, float $to): string
    {
        $fromPrecision = $this->getPrecisionValue->execute($filterSetting, $from);
        $toPrecision = $this->getPrecisionValue->execute($filterSetting, $to);

        return '%.' . $fromPrecision . 'f-%.' . $toPrecision . 'f';
    }

    private function prepareFromToItemData(string $facetedKey): array
    {
        [$from, $to] = explode('_', $facetedKey);
        $from = $from == '*' ? 0 : (float)$from;
        $to = $to == '*' ? '' : (float)$to;

        return  [$from, $to];
    }

    /**
     * @return array|null
     * @throws LocalizedException
     */
    private function getFacetedData(): ?array
    {
        if ($this->facetedData === null) {
            $productCollection = $this->getLayer()->getProductCollection();
            try {
                $this->facetedData = $productCollection->getFacetedData(
                    $this->getAttributeModel()->getAttributeCode(),
                    $this->getSearchResult()
                );
            } catch (StateException $e) {
                if (!$this->messageManager->hasMessages()) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Make sure that "%1" attribute can be used in layered navigation',
                            $this->getAttributeModel()->getAttributeCode()
                        )
                    );
                }
                $this->facetedData = [];
            }
        }

        return $this->facetedData;
    }

    /**
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return string
     */
    protected function renderRangeLabel($fromPrice, $toPrice): string
    {
        return $this->renderLabelDependOnPrice((float) $fromPrice, (float) $toPrice);
    }

    /**
     * method is used for Amasty\GroupedOptions\Plugin\Shopby\Model\Layer\Filter\Price plugin
     * @param float $fromPrice
     * @param float $toPrice
     *
     * @return string
     */
    public function renderLabelDependOnPrice(float $fromPrice, float $toPrice): string
    {
        $defaultLabel = $this->getDefaultRangeLabel($fromPrice, $toPrice);

        if ($defaultLabel) {
            return $defaultLabel;
        }

        $stateLabel = $this->getRangeLabel($fromPrice, $toPrice);

        return (string) $stateLabel;
    }

    /**
     * @param float $fromPrice
     * @param float $toPrice
     * @return string
     */
    private function getDefaultRangeLabel(float $fromPrice, float $toPrice): string
    {
        $result = '';
        $filterSetting = $this->filterSettingResolver->getFilterSetting($this);

        if ($filterSetting->getUnitsLabelUseCurrencySymbol()) {
            $fromPrecision = $this->getPrecisionValue->execute($filterSetting, $fromPrice);
            $toPrecision = $this->getPrecisionValue->execute($filterSetting, $toPrice);
            $formattedFromPrice = $this->currency->formatPrecision($fromPrice, $fromPrecision);
            $formattedToPrice = $this->currency->formatPrecision($toPrice, $toPrecision);

            $result = $this->getFormattedString($toPrice, $formattedFromPrice, $formattedToPrice);
        }

        return (string) $result;
    }

    /**
     * @param $fromPrice
     * @param $toPrice
     */
    private function getRangeLabel(float $fromPrice, float $toPrice): string
    {
        $formattedFromPrice = $this->formatLabelForStateAndRange($fromPrice);
        $formattedToPrice = $this->formatLabelForStateAndRange($toPrice);

        return (string)$this->getFormattedString($toPrice, $formattedFromPrice, $formattedToPrice);
    }

    private function getFormattedString(float $toPrice, string $formattedFromPrice, string $formattedToPrice): Phrase
    {
        if (!$toPrice) {
            $result = __('%1 and above', $formattedFromPrice);
        } else {
            $result =  __(
                '%1 - %2',
                $formattedFromPrice,
                $formattedToPrice
            );
        }

        return $result;
    }

    private function formatLabelForStateAndRange(float $value): string
    {
        $filterSetting = $this->filterSettingResolver->getFilterSetting($this);
        $value = round((float) $value, 2);
        $format = '%.' . $this->getPrecisionValue->execute($filterSetting, $value) . 'F';

        if ($filterSetting->getPositionLabel() == PositionLabel::POSITION_BEFORE) {
            $format = '%s' . $format;
            $formattedLabel = sprintf($format, $filterSetting->getUnitsLabel(), $value);
        } else {
            $format = $format . '%s';
            $formattedLabel = sprintf($format, $value, $filterSetting->getUnitsLabel());
        }

        return $formattedLabel;
    }

    /**
     * @return null
     */
    public function getCurrentFrom(): ?float
    {
        return $this->decimalRequestDataResolver->getCurrentFrom($this);
    }

    /**
     * @return null
     */
    public function getCurrentTo(): ?float
    {
        return $this->decimalRequestDataResolver->getCurrentTo($this);
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        $itemsCount = $this->decimalFilterSettingResolver->isIgnoreRanges($this) ? 0 : parent::getItemsCount();

        if ($itemsCount == 0) {
            /**
             * show up filter event don't have any option
             */
            $fromToConfig = $this->getFromToConfig();
            if ($fromToConfig && $fromToConfig['min'] != $fromToConfig['max']) {
                return 1;
            }
        }

        return $itemsCount;
    }

    private function getSearchResult(): ?SearchResultInterface
    {
        $alteredQueryResponse = null;
        if ($this->filterRequestDataResolver->hasCurrentValue($this)) {
            $attributeCodesToExclude = [];
            foreach ($this->filterRequestDataResolver->getCurrentValue($this) as $key => $value) {
                $attributeCode = $this->getFakeAttributeCodeForApply($key);
                $attributeCodesToExclude[] = $attributeCode . '.from';
                $attributeCodesToExclude[] = $attributeCode . '.to';
            }

            $searchCriteria = $this->getLayer()->getProductCollection()->getSearchCriteria($attributeCodesToExclude);
            $alteredQueryResponse = $this->search->search($searchCriteria);
        }

        return $alteredQueryResponse;
    }

    private function getFakeAttributeCodeForApply(int $key): string
    {
        $attributeCode = $this->getAttributeModel()->getAttributeCode();
        if ($key > 0) {
            $attributeCode .= ShopbyRequestGenerator::FAKE_SUFFIX . $key;
        }

        return $attributeCode;
    }
}
