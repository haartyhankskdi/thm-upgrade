<?php 

namespace Kdi\CustomExport\Model\ResourceModel\CustomExport;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
        /**
         * Define resource model
         *
         * @return void
         */
        public function __construct(
	        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
	        \Psr\Log\LoggerInterface $logger,
	        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
	        \Magento\Framework\Event\ManagerInterface $eventManager,
	        \Magento\Store\Model\StoreManagerInterface $storeManager,
	        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
	        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
	    ) {
	        $this->_init(
	            'Kdi\CustomExport\Model\CustomExport',
	            'Kdi\CustomExport\Model\ResourceModel\CustomExport'
	        );
	        parent::__construct(
	            $entityFactory, $logger, $fetchStrategy, $eventManager, $connection,
	            $resource
	        );
	    }

	    protected function _initSelect()
		{
		    $this->addFilterToMap('entity_id', 'main_table.entity_id');
		    $this->addFilterToMap('created_at', 'main_table.created_at');
		    parent::_initSelect();

		    return $this->getSelect(

		    )->joinLeft(
		        ['thirdTable' => $this->getTable('sales_order_item')],
		        'main_table.entity_id = thirdTable.order_id AND thirdTable.product_type = \'simple\'',
		        ['sku','name','product_options']
		    )
			//for billing address
			// ->joinLeft(
		    //     ['secondTable' => $this->getTable('sales_order_address')],
		    //     'main_table.entity_id = secondTable.parent_id AND secondTable.address_type = \'billing\'',
		    //     ['customer_dob' => "date(customer_dob)",'country_id','bill_name' => "CONCAT(secondTable.firstname, ' ', secondTable.lastname)",'billing_address' => "CONCAT(secondTable.street, ' ', secondTable.city, ' ',secondTable.region, ' ',secondTable.postcode)"]  
		    // )
			->joinLeft(
		        ['secondsTable' => $this->getTable('sales_order_address')],
		        'main_table.entity_id = secondsTable.parent_id AND secondsTable.address_type = \'shipping\'',
		        ['ship_name' => "CONCAT(secondsTable.firstname, ' ', secondsTable.lastname)",'shipping_address' => "CONCAT(secondsTable.street, ' ', secondsTable.city, ' ',secondsTable.region, ' ',secondsTable.postcode)"]      
		    )->joinLeft(
		        ['fourthTable' => $this->getTable('newsletter_subscriber')],
		        'main_table.customer_id = fourthTable.customer_id',
		        ['subscriber_status']		    
		    )->joinLeft(
		        ['fivthTable' => $this->getTable('sales_shipment_track')],
		        'main_table.entity_id = fivthTable.order_id',
		        ['track_number']   		
		    )->order('main_table.created_at DESC');  

		}
}

?>