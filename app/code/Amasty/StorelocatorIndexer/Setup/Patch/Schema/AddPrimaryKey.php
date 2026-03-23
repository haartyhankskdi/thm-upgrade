<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Indexer for Magento 2 (System)
 */

namespace Amasty\StorelocatorIndexer\Setup\Patch\Schema;

use Amasty\StorelocatorIndexer\Model\ResourceModel\LocationProductIndex;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddPrimaryKey implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $setup;

    /**
     * @param SchemaSetupInterface $setup
     */
    public function __construct(
        SchemaSetupInterface $setup
    ) {
        $this->setup = $setup;
    }

    /**
     * @return $this
     */
    public function apply()
    {
        $this->setup->getConnection()->addColumn(
            $this->setup->getTable(LocationProductIndex::TABLE_NAME),
            'id',
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Id',
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        );

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }
}
