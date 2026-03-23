<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Cron;

use Amasty\Gdpr\Api\Data\ActionLogInterface;
use Amasty\Gdpr\Model\CleaningDate;
use Amasty\Gdpr\Model\ResourceModel\ActionLog;
use Magento\Framework\App\ResourceConnection;

class ClearLog
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CleaningDate
     */
    private $cleaningDate;

    public function __construct(
        ResourceConnection $resourceConnection,
        CleaningDate $cleaningDate
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->cleaningDate = $cleaningDate;
    }

    public function clearLog()
    {
        if (!$dateForRemove = $this->cleaningDate->getAutoCleaningDate()) {
            return;
        }
        $tableName = $this->resourceConnection->getTableName(ActionLog::TABLE_NAME);
        $this->resourceConnection->getConnection()->delete(
            $tableName,
            [ActionLogInterface::CREATED_AT . ' < ?' => $dateForRemove]
        );
    }
}
