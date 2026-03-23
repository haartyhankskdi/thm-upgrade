<?php 

namespace MY\CustomerSurvey\Model\ResourceModel\CustomerSurvey;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
        /**
         * Define resource model
         *
         * @return void
         */
        protected function _construct()
        {
                $this->_init('MY\CustomerSurvey\Model\CustomerSurvey', 'MY\CustomerSurvey\Model\ResourceModel\CustomerSurvey');
        }
}

?>