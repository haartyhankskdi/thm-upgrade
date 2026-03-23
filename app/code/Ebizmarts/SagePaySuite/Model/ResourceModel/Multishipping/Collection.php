<?php

namespace Ebizmarts\SagePaySuite\Model\ResourceModel\Multishipping;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    // @codingStandardsIgnoreStart
    public function _construct()
    {
        $this->_init(
            'Ebizmarts\SagePaySuite\Model\MsScaData',
            'Ebizmarts\SagePaySuite\Model\ResourceModel\MsScaData'
        );
    }
    // @codingStandardsIgnoreEnd
}
