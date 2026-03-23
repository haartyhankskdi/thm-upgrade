<?php

namespace Ebizmarts\SagePaySuite\Model\ResourceModel\SyncFromApi;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Ebizmarts\SagePaySuite\Model\SyncFromApi::class,
            \Ebizmarts\SagePaySuite\Model\ResourceModel\SyncFromApi::class
        );
    }
}
