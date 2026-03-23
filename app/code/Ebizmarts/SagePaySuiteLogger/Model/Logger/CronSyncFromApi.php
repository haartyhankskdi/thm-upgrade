<?php

namespace Ebizmarts\SagePaySuiteLogger\Model\Logger;

class CronSyncFromApi extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/SagePaySuite/CronSyncFromApi.log'; // @codingStandardsIgnoreLine

    /**
     * @param array $record
     * @return bool
     */
    public function isHandling(array $record) : bool
    {
        return $record['level'] == \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger::LOG_CRON_SYNC_FROM_API;
    }
}
