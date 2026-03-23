<?php

namespace Ebizmarts\SagePaySuiteLogger\Model\Logger;

use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\Logger\Handler\Base;

class Exception extends Base
{
    /**
     * File name
     * @var string
     */

    protected $fileName = '/var/log/SagePaySuite/Exception.log'; // @codingStandardsIgnoreLine

    public function isHandling(array $record) : bool
    {
        return $record['level'] == Logger::LOG_EXCEPTION;
    }
}
