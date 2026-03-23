<?php 

namespace Kdi\CustomExport\Model\ResourceModel;

class CustomExport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

 public function _construct()
 {

 	$this->_init("sales_order","entity_id");

 }

}

 ?>