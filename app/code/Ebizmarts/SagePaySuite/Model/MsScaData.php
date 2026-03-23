<?php


namespace Ebizmarts\SagePaySuite\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class MsScaData extends \Magento\Framework\Model\AbstractModel
{
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_init(\Ebizmarts\SagePaySuite\Model\ResourceModel\MsScaData::class);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    public function _construct()
    {
        $this->_init('Ebizmarts\SagePaySuite\Model\ResourceModel\MsScaData');
    }
    // @codingStandardsIgnoreEnd
}
