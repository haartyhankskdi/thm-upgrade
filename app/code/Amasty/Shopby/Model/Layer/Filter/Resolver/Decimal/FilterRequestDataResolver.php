<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer\Filter\Resolver\Decimal;

use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver as DefaultFilterRequestDataResolver;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class FilterRequestDataResolver
{
    /**
     * @var DefaultFilterRequestDataResolver
     */
    private $filterRequestDataResolver;

    public function __construct(DefaultFilterRequestDataResolver $filterRequestDataResolver)
    {
        $this->filterRequestDataResolver = $filterRequestDataResolver;
    }

    public function setFromTo(FilterInterface $filter, float $from, ?float $to = null): void
    {
        $this->filterRequestDataResolver->setCurrentValue($filter, $this->prepareFromTo($from, $to));
    }

    public function addFromTo(FilterInterface $filter, float $from, ?float $to = null): void
    {
        $this->filterRequestDataResolver->addCurrentValue($filter, $this->prepareFromTo($from, $to));
    }

    /**
     * @param FilterInterface $filter
     * @return float|null
     */
    public function getCurrentFrom(FilterInterface $filter): ?float
    {
        $currentValue = $this->filterRequestDataResolver->getCurrentValue($filter);

        $minFrom = PHP_INT_MAX;
        if (is_array($currentValue)) {
            foreach ($currentValue as $value) {
                if (!isset($value['from'])) {
                    continue;
                }
                $minFrom = min($minFrom, $value['from']);
            }
        }

        return $minFrom !== PHP_INT_MAX ? $minFrom : null;
    }

    /**
     * @param FilterInterface $filter
     * @return float|null
     */
    public function getCurrentTo(FilterInterface $filter): ?float
    {
        $currentValue = $this->filterRequestDataResolver->getCurrentValue($filter);

        $maxTo = 0;
        if (is_array($currentValue)) {
            foreach ($currentValue as $value) {
                if (!isset($value['to'])) {
                    continue;
                }
                $maxTo = max($maxTo, $value['to']);
            }
        }

        return $maxTo > 0 ? $maxTo : null;
    }

    public function getDelta(FilterInterface $filter, bool $useFrom = true): float
    {
        $filterParamName = $useFrom ? 'df' : 'dt';
        return (float) $this->filterRequestDataResolver->getDeltaParam($filterParamName);
    }

    public function getValidFilterValue(string $filterValue): array
    {
        $filterValues = explode('-', $filterValue);
        if (count($filterValues) != 2) {
            return [];
        }
        foreach ($filterValues as $value) {
            if ($value !== ''
                && $value !== '0'
                && $value !== '0.00'
                && (!is_numeric($value) || (double)$value <= 0 || is_infinite((double)$value))) {
                return [];
            }
        }

        return $filterValues;
    }

    private function prepareFromTo(float $from, ?float $to = null): array
    {
        if ($to && $from > $to) {
            $toTmp = $to;
            $to = $from;
            $from = $toTmp;
        }

        return ['from' => $from, 'to' => $to];
    }
}
