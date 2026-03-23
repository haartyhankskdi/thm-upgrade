<?php 

namespace MY\CompleteSurvey\Model;
 
class CompleteSurvey extends \Magento\Framework\Model\AbstractModel
{
        protected function _construct()
        {
                $this->_init('MY\CompleteSurvey\Model\ResourceModel\CompleteSurvey');
        }
}

?>