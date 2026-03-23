<?php

namespace Ebizmarts\SagePaySuite\Model\ResourceModel\Token;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ebizmarts\SagePaySuite\Model\Token;
use Ebizmarts\SagePaySuite\Model\ResourceModel\Token as TokenResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            Token::class,
            TokenResource::class
        );
    }
}
