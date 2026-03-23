<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Cron;

use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\ResourceModel\CookieConsent;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;

class ClearLog
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        ResourceConnection $resourceConnection,
        DateTime $dateTime,
        ConfigProvider $configProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configProvider = $configProvider;
        $this->dateTime = $dateTime;
    }

    public function clearLog()
    {
        $days = $this->configProvider->getAutoCleaningDays();
        $time = '-' . $days . ' days';
        $dateForRemove = $this->dateTime->gmtDate('Y-m-d H:i:s', strtotime($time));
        $tableName = $this->resourceConnection->getTableName(CookieConsent::TABLE_NAME);
        $this->resourceConnection->getConnection()->delete(
            $tableName,
            ['date_recieved < ?' => $dateForRemove]
        );
    }
}
