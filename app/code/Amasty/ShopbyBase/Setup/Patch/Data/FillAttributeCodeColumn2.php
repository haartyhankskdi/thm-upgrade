<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Setup\Patch\Data;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Helper\FilterSetting as FilterSettingHelper;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting as FilterSettingResource;
use Magento\Framework\DB\Sql\ColumnValueExpression;
use Magento\Framework\DB\Sql\ColumnValueExpressionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Duplicate FillAttributeCodeColumn.
 * Necessary to fill the column in case the original patch was done incorrectly.
 */
class FillAttributeCodeColumn2 implements DataPatchInterface
{
    /**
     * @var FilterSettingResource
     */
    private FilterSettingResource $filterSettingResource;

    /**
     * @var ColumnValueExpressionFactory
     */
    private ColumnValueExpressionFactory $columnValueExpressionFactory;

    public function __construct(
        FilterSettingResource $filterSettingResource,
        ColumnValueExpressionFactory $columnValueExpressionFactory
    ) {
        $this->filterSettingResource = $filterSettingResource;
        $this->columnValueExpressionFactory = $columnValueExpressionFactory;
    }

    /**
     * @return DataPatchInterface
     */
    public function apply()
    {
        $filterCodesWithEmptyAttributeCode = $this->getFilterCodesWithEmptyAttributeCode();
        if (empty($filterCodesWithEmptyAttributeCode)) {
            return $this;
        }

        $filterCodesWithAttributeCode = $this->getFilterCodesWithAttributeCode($filterCodesWithEmptyAttributeCode);

        $connection = $this->filterSettingResource->getConnection();

        $filterCodesWithoutDuplicates = array_diff($filterCodesWithEmptyAttributeCode, $filterCodesWithAttributeCode);
        if ($filterCodesWithoutDuplicates) {
            $connection->update(
                $this->filterSettingResource->getMainTable(),
                [FilterSettingInterface::ATTRIBUTE_CODE => $this->getExpression()],
                [sprintf('%s IN (?)', FilterSettingInterface::FILTER_CODE) => $filterCodesWithoutDuplicates]
            );
        }

        $connection->delete(
            $this->filterSettingResource->getMainTable(),
            [$connection->prepareSqlCondition(FilterSettingInterface::ATTRIBUTE_CODE, ['null' => true])]
        );

        return $this;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [FillAttributeCodeColumn::class];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return string[]
     */
    private function getFilterCodesWithEmptyAttributeCode(): array
    {
        $connection = $this->filterSettingResource->getConnection();

        $select = $this->filterSettingResource->getConnection()->select()->from(
            $this->filterSettingResource->getMainTable(),
            [FilterSettingInterface::FILTER_CODE]
        );
        $select->where(sprintf(
            '%s IS NULL',
            FilterSettingInterface::ATTRIBUTE_CODE
        ));

        return $connection->fetchCol($select);
    }

    /**
     * @param string[] $filterCodesWithEmptyAttributeCode
     * @return string[]
     */
    private function getFilterCodesWithAttributeCode(array $filterCodesWithEmptyAttributeCode): array
    {
        $connection = $this->filterSettingResource->getConnection();

        $select = $connection->select()->from(
            $this->filterSettingResource->getMainTable(),
            [FilterSettingInterface::FILTER_CODE]
        );
        $select->where(
            sprintf('%s IN (?)', FilterSettingInterface::FILTER_CODE),
            $filterCodesWithEmptyAttributeCode
        );
        $select->where(sprintf(
            '%s IS NOT NULL',
            FilterSettingInterface::ATTRIBUTE_CODE
        ));

        return $connection->fetchCol($select);
    }

    /**
     * Get attribute code expression.
     */
    private function getExpression(): ColumnValueExpression
    {
        $connection = $this->filterSettingResource->getConnection();

        $condition = sprintf(
            'IF (LEFT(%s, 5) = %s, SUBSTR(%1$s, 6), %1$s)',
            FilterSettingInterface::FILTER_CODE,
            $connection->quote(FilterSettingHelper::ATTR_PREFIX)
        );

        return $this->columnValueExpressionFactory->create(['expression' => $condition]);
    }
}
