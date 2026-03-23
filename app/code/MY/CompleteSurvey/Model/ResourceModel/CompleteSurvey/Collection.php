<?php 

namespace MY\CompleteSurvey\Model\ResourceModel\CompleteSurvey;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
        /**
         * Define resource model
         *
         * @return void
         */
        protected function _construct()
        {
                $this->_init('MY\CompleteSurvey\Model\CompleteSurvey', 'MY\CompleteSurvey\Model\ResourceModel\CompleteSurvey');
        }
}

?>