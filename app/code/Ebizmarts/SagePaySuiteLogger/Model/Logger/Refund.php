<?php

namespace Ebizmarts\SagePaySuiteLogger\Model\Logger;

class Refund extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/SagePaySuite/Refund.log'; // @codingStandardsIgnoreLine

    public function isHandling(array $record) : bool
    {
        return $record['level'] == \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger::LOG_REFUND;
    }
}
