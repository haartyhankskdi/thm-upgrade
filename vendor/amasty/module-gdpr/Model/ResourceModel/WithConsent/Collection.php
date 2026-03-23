<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel\WithConsent;

use Amasty\Gdpr\Api\Data\WithConsentInterface;
use Amasty\Gdpr\Model\Consent\Consent;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\ConsentLogger;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Amasty\Gdpr\Model\Consent\ConsentStore\ResourceModel\ConsentStore as ResourceConsentStore;
use Amasty\Gdpr\Model\Consent\ResourceModel\Consent as ResourceConsent;
use Magento\Store\Model\Store;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Gdpr\Model\WithConsent::class,
            \Amasty\Gdpr\Model\ResourceModel\WithConsent::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function getConsentCustomerIds(): array
    {
        $this->getSelect()->group('customer_id');

        return $this->getColumnValues('customer_id');
    }

    /**
     * Filter to receive last status of each consent code of each customer
     */
    public function filterByLastConsentRecord(): Collection
    {
        $this->getSelect()->joinLeft(
            ['m2' => $this->getTable($this->getMainTable())],
            'main_table.' . WithConsentInterface::CUSTOMER_ID . ' = m2.' . WithConsentInterface::CUSTOMER_ID
            . ' AND main_table.' . WithConsentInterface::CONSENT_CODE . ' = m2.' . WithConsentInterface::CONSENT_CODE
            . ' AND main_table.' . WithConsentInterface::ID . ' < m2.' . WithConsentInterface::ID,
            []
        )->where('m2.id IS NULL');

        return $this;
    }

    public function filterByPolicyVersionAndLinkType(string $policyVersion, int $storeId): Collection
    {
        $connection = $this->getConnection();
        $currentStoreField = 's.' . ConsentStore::LINK_TYPE;
        $defaultStoreField = 's_default.' . ConsentStore::LINK_TYPE;
        $linkTypeField = new \Zend_Db_Expr("IFNULL($currentStoreField, $defaultStoreField)");
        $conditionWhen = $connection->quoteIdentifier($linkTypeField)
            . ' = ' . $connection->quote(ConsentLinkType::PRIVACY_POLICY);
        $conditionThen = $connection->quoteIdentifier('main_table.' . WithConsentInterface::POLICY_VERSION)
            . ' = ' . $connection->quote($policyVersion);
        $conditionElse =  $connection->quoteIdentifier('main_table.' . WithConsentInterface::POLICY_VERSION)
            . ' = ' . $connection->quote(ConsentLogger::CMS_PAGE);
        $casesResults = [$conditionWhen  => $conditionThen];
        $conditionLinkType = $connection->getCaseSql('', $casesResults, $conditionElse);

        $this->getSelect()->joinInner(
            ['c' => $this->getTable(ResourceConsent::TABLE_NAME)],
            'main_table.' . WithConsentInterface::CONSENT_CODE . ' = c.' . Consent::CONSENT_CODE,
            []
        )->joinLeft(
            ['s' => $this->getTable(ResourceConsentStore::TABLE_NAME)],
            'c.' . Consent::ID . ' = s.' . ConsentStore::CONSENT_ENTITY_ID .
            ' AND s.' . ConsentStore::CONSENT_STORE_ID . ' = ' . $storeId,
            []
        )->join(
            ['s_default' => $this->getTable(ResourceConsentStore::TABLE_NAME)],
            'c.' . Consent::ID . ' = s_default.' . ConsentStore::CONSENT_ENTITY_ID,
            []
        )->where(
            's_default.' . ConsentStore::CONSENT_STORE_ID . ' = ?',
            Store::DEFAULT_STORE_ID
        )->where($conditionLinkType);

        return $this;
    }
}
