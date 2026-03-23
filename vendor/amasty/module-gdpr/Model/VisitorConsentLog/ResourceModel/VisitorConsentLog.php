<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\VisitorConsentLog\ResourceModel;

use Amasty\Gdpr\Model\VisitorConsentLog\VisitorConsentLog as VisitorConsentLogModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as Date;

class VisitorConsentLog extends AbstractDb
{
    public const TABLE_NAME = 'amasty_gdpr_visitor_consent_log';

    /**
     * @var Date
     */
    private $date;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        Context $context,
        Date $date,
        DateTime $dateTime,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->date = $date;
        $this->dateTime = $dateTime;
    }

    protected function _construct()
    {
        $this->_init(
            self::TABLE_NAME,
            VisitorConsentLogModel::ID
        );
    }

    protected function _beforeSave(AbstractModel $object)
    {
        $customerId = (int)$object->getData(VisitorConsentLogModel::CUSTOMER_ID);
        $sessionId = (string)$object->getData(VisitorConsentLogModel::SESSION_ID);
        $websiteId = (int)$object->getData(VisitorConsentLogModel::WEBSITE_ID);
        $this->deleteCustomerRecords($customerId, $sessionId, $websiteId);

        return parent::_beforeSave($object);
    }

    private function getPolicyVersion(string $field, $value, ?int $websiteId): string
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                $this->getMainTable(),
                VisitorConsentLogModel::POLICY_VERSION
            )->where(VisitorConsentLogModel::WEBSITE_ID . ' = ?', (int)$websiteId)
            ->where($connection->quoteIdentifier($field) . ' = ?', $value)
            ->order(VisitorConsentLogModel::DATE_CONSENTED . ' DESC');

        return (string)$connection->fetchOne($select);
    }

    public function getPolicyVersionBySessionId(string $sessionId, ?int $websiteId): string
    {
        return $this->getPolicyVersion(VisitorConsentLogModel::SESSION_ID, $sessionId, $websiteId);
    }

    public function getPolicyVersionByCustomerId(int $customerId, ?int $websiteId): string
    {
        return $this->getPolicyVersion(VisitorConsentLogModel::CUSTOMER_ID, $customerId, $websiteId);
    }

    public function getCustomerPolicyVersion(?int $customerId, ?string $sessionId, ?int $websiteId): string
    {
        $version = '';
        if (!empty($customerId)) {
            $version = $this->getPolicyVersionByCustomerId((int)$customerId, $websiteId);
        } elseif (!empty($sessionId)) {
            $version = $this->getPolicyVersionBySessionId((string)$sessionId, $websiteId);
        }

        return $version;
    }

    private function deleteRecord(string $field, $value, ?int $websiteId): bool
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            [
                VisitorConsentLogModel::WEBSITE_ID . ' = ?' => (int)$websiteId,
                $connection->quoteIdentifier($field) . ' = ?' => $value
            ]
        );

        return true;
    }

    public function deleteBySessionId(string $sessionId, ?int $websiteId): bool
    {
        return $this->deleteRecord(VisitorConsentLogModel::SESSION_ID, $sessionId, $websiteId);
    }

    public function deleteByCustomerId(int $customerId, ?int $websiteId): bool
    {
        return $this->deleteRecord(VisitorConsentLogModel::CUSTOMER_ID, $customerId, $websiteId);
    }

    public function deleteCustomerRecords(?int $customerId, ?string $sessionId, ?int $websiteId): bool
    {
        $result = false;
        if (!empty($customerId)) {
            $result = $this->deleteByCustomerId((int)$customerId, $websiteId);
        } elseif (!empty($sessionId)) {
            $result = $this->deleteBySessionId((string)$sessionId, $websiteId);
        }

        return $result;
    }

    public function clear(int $cleanTime): bool
    {
        if (!$cleanTime) {
            return false;
        }

        $connection = $this->getConnection();
        $timeLimit = $this->dateTime->formatDate($this->date->gmtTimestamp() - $cleanTime);
        $connection->delete(
            $this->getMainTable(),
            [
                VisitorConsentLogModel::DATE_CONSENTED . ' < ?' => $timeLimit,
                VisitorConsentLogModel::CUSTOMER_ID . ' IS NULL'
            ]
        );

        return true;
    }

    public function updateSessionId(string $oldSessionId, string $newSessionId): bool
    {
        if (empty($oldSessionId) || empty($newSessionId)) {
            return false;
        }

        $connection = $this->getConnection();
        $connection->update(
            $this->getMainTable(),
            [
                VisitorConsentLogModel::SESSION_ID => $newSessionId
            ],
            [
                VisitorConsentLogModel::SESSION_ID . ' = ?' => $oldSessionId,
                VisitorConsentLogModel::CUSTOMER_ID . ' IS NULL'
            ]
        );

        return true;
    }
}
