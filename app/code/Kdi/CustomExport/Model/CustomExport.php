<?php 

namespace Kdi\CustomExport\Model;
 
class CustomExport extends \Magento\Framework\Model\AbstractModel
{
        protected function _construct()
        {
                $this->_init('Kdi\CustomExport\Model\ResourceModel\CustomExport');
        }
}

?>