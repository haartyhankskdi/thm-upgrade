<?php

namespace Ebizmarts\BrippoPayments\Model\ResourceModel\StripeCardFingerprints;

use Ebizmarts\BrippoPayments\Model\StripeCardFingerprints;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            StripeCardFingerprints::class,
            \Ebizmarts\BrippoPayments\Model\ResourceModel\StripeCardFingerprints::class
        );
    }
}
