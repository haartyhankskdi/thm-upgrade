<?php 

namespace MY\CustomerSurvey\Model;
 
class CustomerSurvey extends \Magento\Framework\Model\AbstractModel
{
        protected function _construct()
        {
                $this->_init('MY\CustomerSurvey\Model\ResourceModel\CustomerSurvey');
        }
}

?>