<?php

namespace MY\CustomerSurvey\Setup;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{

	public function Upgrade(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if(version_compare($context->getVersion(), '1.1.1', '<')) { 
		if (!$installer->tableExists('my_customersurvey')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('my_customersurvey')
			)->addColumn(
				'id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				[
					'identity' => true,
					'nullable' => false,
					'primary'  => true,
					'unsigned' => true,
				],
				'Customer Survey ID'
			)->addColumn(
				'order_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Order ID'
			)->addColumn(
				'website_navigation',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Website Easy To Navigate'
			)->addColumn(
				'improvement',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Improvement'
			)->addColumn(
				'created_at',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
				null,
				['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
				'Created At'
			)->setComment('Customer Survey Form Table');
			$installer->getConnection()->createTable($table);

			$installer->getConnection()->addIndex(
				$installer->getTable('my_customersurvey'),
				$setup->getIdxName(
					$installer->getTable('my_customersurvey'),
					['website_navigation', 'improvement'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['website_navigation', 'improvement'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
		}
	}
		$installer->endSetup();
	}
}