<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\StoreData;

use Magento\Framework\App\ResourceConnection;

class ScopedFieldsProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $entityRelationMapping;

    public function __construct(
        ResourceConnection $resourceConnection,
        array $entityRelationMapping = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->entityRelationMapping = $this->normalizeRelationMapping($entityRelationMapping);
    }

    public function getScopedFields(string $mainEntityTable): array
    {
        if (!isset($this->entityRelationMapping[$mainEntityTable])) {
            return [];
        }

        $scopedFields = [];
        $connection = $this->resourceConnection->getConnection();
        $scopeEntityTable = $this->entityRelationMapping[$mainEntityTable];

        foreach ($connection->describeTable($scopeEntityTable) as $columnName => $columnConfig) {
            if ($columnConfig['NULLABLE'] ?? false) {
                $scopedFields[] = $columnName;
            }
        }

        return $scopedFields;
    }

    private function normalizeRelationMapping(array $relationMapping): array
    {
        $mapping = [];

        foreach ($relationMapping as $entityType) {
            $entityTable = $this->resourceConnection->getTableName($entityType['entityTable']);
            $mapping[$entityTable] = $this->resourceConnection->getTableName($entityType['storeEntityTable']);
        }

        return $mapping;
    }
}
