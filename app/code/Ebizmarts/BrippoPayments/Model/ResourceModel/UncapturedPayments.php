<?php

namespace Ebizmarts\BrippoPayments\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Ebizmarts\BrippoPayments\Api\Data\UncapturedPaymentsInterface;

class UncapturedPayments extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('brippo_uncaptured_payments', 'id');
    }

    public function update(\Ebizmarts\BrippoPayments\Model\UncapturedPayments $row)
    {
        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            $row->getData(),
            [UncapturedPaymentsInterface::COUNT]
        );
    }
}
