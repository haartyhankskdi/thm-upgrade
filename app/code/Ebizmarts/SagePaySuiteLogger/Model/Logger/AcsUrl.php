<?php

namespace Ebizmarts\SagePaySuiteLogger\Model\Logger;

class AcsUrl extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/SagePaySuite/AcsUrl.log'; // @codingStandardsIgnoreLine

    public function isHandling(array $record) : bool
    {
        return $record['level'] == \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger::LOG_ACS_URL;
    }
}
