<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel;

use Amasty\Gdpr\Api\Data\ConsentQueueInterface;
use Amasty\Gdpr\Model\ConsentQueue as ConsentQueueModel;
use Amasty\Gdpr\Model\VisitorConsentLog\ResourceModel\VisitorConsentLog as VisitorConsentLogResource;
use Amasty\Gdpr\Model\VisitorConsentLog\VisitorConsentLog as VisitorConsentLogModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ConsentQueue extends AbstractDb
{
    public const TABLE_NAME = 'amasty_gdpr_consent_queue';

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, ConsentQueueInterface::ID);
    }

    public function clear(): void
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }

    public function resetFailStatus(array $ids = []): int
    {
        return $this->getConnection()->update(
            $this->getMainTable(),
            [ConsentQueueInterface::STATUS => ConsentQueueModel::STATUS_PENDING],
            [
                ConsentQueueInterface::STATUS . '=?' => ConsentQueueModel::STATUS_FAIL,
                ConsentQueueInterface::ID . ' IN(?)' => $ids
            ]
        );
    }

    public function generateQueue(): void
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                $this->getTable(VisitorConsentLogResource::TABLE_NAME),
                [VisitorConsentLogModel::CUSTOMER_ID]
            )->where(
                VisitorConsentLogModel::CUSTOMER_ID . ' IS NOT NULL'
            )->distinct(true);

        $query = $connection->insertFromSelect(
            $select,
            $this->getMainTable(),
            [ConsentQueueInterface::CUSTOMER_ID]
        );
        $connection->query($query);
    }
}
