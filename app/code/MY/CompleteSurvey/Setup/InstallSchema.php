<?php

namespace MY\CompleteSurvey\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('my_completesurvey')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('my_completesurvey')
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
				'customer_email_sent',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Customer Email Sent'				
			)->addColumn(
				'customer_email',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Customer Email'
			)->addColumn(
				'customer_fname',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Customer First Name'
			)->addColumn(
				'customer_lname',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Customer Last Name'		
			)->addColumn(
				'recommend_complete_experience',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Recommend Complete Experience'
			)->addColumn(
				'recommend_friend',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Recommend Friend'
			)->addColumn(
				'improve_experience',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Improve Experience'
			)->addColumn(
				'learn_website',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Learn Website'	
			)->addColumn(
				'shopping',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Shopping'
			)->addColumn(
				'looking_for',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Looking For'
			)->addColumn(
				'find_looking_for',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Find Looking For'		
			)->addColumn(
				'visit_website',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Visit Website'	
			)->addColumn(
				'navigate',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Navigate'	
			)->addColumn(
				'rate_experience',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Rate Experience'
			)->addColumn(
				'product_range',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Product Range'
			)->addColumn(
				'product_detail',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Product Detail'
			)->addColumn(
				'product_alternatives',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Product Alternatives'
			)->addColumn(
				'product_quality',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Product Quality'
			)->addColumn(
				'order_packaging',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Order Packaging'
			)->addColumn(
				'delivery_service',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Delivery Service'	
			)->addColumn(
				'pack_del_service',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Pack Delivery Service'	
			)->addColumn(
				'other_product_store',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Other Product Store'
			)->addColumn(
				'share_card_detail',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Share Card Detail'
			)->addColumn(
				'buy_again',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Buy Again'
			)->addColumn(
				'paid_membership',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'Paid Membership'															
			)->addColumn(
				'created_at',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
				null,
				['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
				'Created At'
			)->setComment('Complete Customer Survey Form Table');
			$installer->getConnection()->createTable($table);

			$installer->getConnection()->addIndex(
				$installer->getTable('my_completesurvey'),
				$setup->getIdxName(
					$installer->getTable('my_completesurvey'),
					['recommend_complete_experience', 'recommend_friend','improve_experience','learn_website','shopping','looking_for','find_looking_for','visit_website','navigate','rate_experience','product_range','product_detail','product_alternatives','product_quality','delivery_service','paid_membership'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['recommend_complete_experience', 'recommend_friend','improve_experience','learn_website','shopping','looking_for','find_looking_for','visit_website','navigate','rate_experience','product_range','product_detail','product_alternatives','product_quality','delivery_service','paid_membership'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
		}
		$installer->endSetup();
	}
}