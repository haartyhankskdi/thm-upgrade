<?php

namespace Ebizmarts\BrippoPayments\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StripeCardFingerprints extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('brippo_payments_card_fingerprints', 'id');
    }
}
