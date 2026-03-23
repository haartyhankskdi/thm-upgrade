<?php 

namespace MY\CustomerSurvey\Model\ResourceModel;

class CustomerSurvey extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

 public function _construct()
 {

 	$this->_init("my_customersurvey","id");

 }

}

 ?>