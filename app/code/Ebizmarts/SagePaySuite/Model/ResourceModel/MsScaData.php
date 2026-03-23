<?php

namespace Ebizmarts\SagePaySuite\Model\ResourceModel;

class MsScaData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    // @codingStandardsIgnoreStart
    public function _construct()
    {
        $this->_init('sagepaysuite_sca_params', 'id');
    }
    // @codingStandardsIgnoreEnd

    public function getScaDataByQuoteId($quoteId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('quote_id=?', $quoteId);

        return $connection->fetchRow($select);
    }
}
