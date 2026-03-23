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

class FillAttributeCodeColumn implements DataPatchInterface
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
        $connection = $this->filterSettingResource->getConnection();

        if (!$connection->isTableExists($this->filterSettingResource->getMainTable())) {
            return $this;
        }

        $connection->update(
            $this->filterSettingResource->getMainTable(),
            [FilterSettingInterface::ATTRIBUTE_CODE => $this->getExpression()]
        );

        return $this;
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
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
