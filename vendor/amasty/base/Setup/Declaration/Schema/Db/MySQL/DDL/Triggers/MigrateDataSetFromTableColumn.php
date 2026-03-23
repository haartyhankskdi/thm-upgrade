<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Setup\Declaration\Schema\Db\MySQL\DDL\Triggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Db\DDLTriggerInterface;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;

/**
 * Migrate a comma separated list of values to separate rows.
 *
 * For use, put the attribute
 * onCreate="amMigrateDataSetFromTableColumn(from_table_name, from_column, target_column)"
 * where from_table_name and from_column is a table and column names of the table that you want to migrate data from.
 *
 * "from_column" is a comma separated list of values.
 *
 * "from_column" should NOT be in the db_schema_whitelist.json,
 * otherwise column already be deleted when the trigger fires and the data will be lost.
 *
 * "from_column" should be deleted from the db_schema.xml to avoid recreation.
 *
 * @since 1.20.0
 */
class MigrateDataSetFromTableColumn implements DDLTriggerInterface
{
    public const MATCH_PATTERN = '/amMigrateDataSetFromTableColumn\(([^\)]+),([^\)]+),([^\)]+)\)/';

    public const BATCH_SIZE = 30000;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function isApplicable(string $statement): bool
    {
        return (bool)preg_match(self::MATCH_PATTERN, $statement);
    }

    public function getCallback(ElementHistory $elementHistory): callable
    {
        $table = $elementHistory->getNew();
        preg_match(self::MATCH_PATTERN, $table->getOnCreate(), $matches);

        return function () use ($table, $matches): void {
            $tableName = $table->getName();
            [, $originTable, $originColumn, $targetColumn] = array_map('trim', $matches);
            $originTable = $this->resourceConnection->getTableName($originTable, $table->getResource());
            $adapter = $this->resourceConnection->getConnection($table->getResource());
            if (!$adapter->isTableExists($originTable) || !$adapter->tableColumnExists($originTable, $originColumn)) {
                return;
            }
            $originIdColumn = $adapter->getAutoIncrementField($originTable);
            $targetIdColumn = current($adapter->describeTable($tableName))['COLUMN_NAME'];

            $select = $adapter->select()->from($originTable, [$originIdColumn, $originColumn]);
            $select->where($originColumn . ' IS NOT NULL');
            $page = 1;
            do {
                $select->limitPage($page++, MigrateDataSetFromTableColumn::BATCH_SIZE);
                $rows = $adapter->fetchAssoc($select);
                $insert = [];
                foreach ($rows as $row) {
                    $id = $row[$originIdColumn];
                    $set = explode(',', $row[$originColumn]);
                    foreach ($set as $value) {
                        $value = trim($value);
                        if ($value !== '') {
                            $insert[] = [
                                $id,
                                $value
                            ];
                        }
                    }
                }

                if ($insert) {
                    $adapter->insertArray($tableName, [$targetIdColumn, $targetColumn], $insert);
                }
            } while (count($rows) === MigrateDataSetFromTableColumn::BATCH_SIZE);

            $adapter->dropColumn($originTable, $originColumn);
        };
    }
}
