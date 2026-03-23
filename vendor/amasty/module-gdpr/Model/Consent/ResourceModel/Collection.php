<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Consent\ResourceModel;

use Amasty\Gdpr\Model\Consent\Consent as ConsentModel;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\ConsentStore\ResourceModel\ConsentStore as ResourceConsentStore;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection
{

    public const NULLABLE_FIELDS = [
        ConsentStore::IS_ENABLED,
        ConsentStore::IS_REQUIRED,
        ConsentStore::LOG_THE_CONSENT,
        ConsentStore::HIDE_CONSENT_AFTER_USER_LEFT_THE_CONSENT,
        ConsentStore::CONSENT_LOCATION,
        ConsentStore::IS_ENABLED,
        ConsentStore::LINK_TYPE,
        ConsentStore::CMS_PAGE_ID,
        ConsentStore::CONSENT_TEXT,
        ConsentStore::COUNTRIES,
        ConsentStore::VISIBILITY,
        ConsentStore::SORT_ORDER
    ];

    /**
     * @var bool
     */
    private $storeDataAdded = false;

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            ConsentModel::class,
            \Amasty\Gdpr\Model\Consent\ResourceModel\Consent::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @return $this
     */
    private function joinDefaultStoreData()
    {
        $consentStoreIdField = ConsentStore::CONSENT_ENTITY_ID;
        $consentIdField = ConsentModel::ID;
        $this->join(
            ['consent_store_config' => $this->getTable(ResourceConsentStore::TABLE_NAME)],
            "main_table.{$consentIdField}=consent_store_config.{$consentStoreIdField}"
        );
        $this->addFieldToFilter(ConsentStore::CONSENT_STORE_ID, Store::DEFAULT_STORE_ID);

        return $this;
    }

    /**
     * @param string $field
     * @param string $value
     *
     * @return Collection|AbstractCollection
     */
    public function addMultiValueFilter(string $field, string $value)
    {
        if ($this->storeDataAdded && in_array($field, self::NULLABLE_FIELDS)) {
            $field = $this->getZendExpressionForField($field);
        }

        return $this->addFieldToFilter(
            $field,
            ['finset' => $value]
        );
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return Collection|AbstractCollection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (!is_array($field)
            && is_string($field)
            && $this->storeDataAdded
            && in_array($field, self::NULLABLE_FIELDS)
        ) {
            $field = $this->getZendExpressionForField($field);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return Collection|AbstractCollection
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if (!is_array($field)
            && is_string($field)
            && $this->storeDataAdded
            && in_array($field, self::NULLABLE_FIELDS)
        ) {
            $field = $this->getZendExpressionForField($field);
        }

        return parent::addOrder($field, $direction);
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function addStoreData(int $storeId = Store::DEFAULT_STORE_ID)
    {
        if ($storeId === Store::DEFAULT_STORE_ID) {
            $this->joinDefaultStoreData();

            return $this;
        }

        $consentId = ConsentModel::ID;
        $consentStoreTableName = ResourceConsentStore::TABLE_NAME;
        $this->addFieldToSelect($consentId);
        $this->addFieldToSelect(ConsentModel::CONSENT_NAME);
        $this->addFieldToSelect(ConsentModel::CONSENT_CODE);

        foreach (self::NULLABLE_FIELDS as $field) {
            $this->addFieldToSelect(
                $this->getZendExpressionForField($field),
                $field
            );
        }

        $storeIdField = ConsentStore::CONSENT_STORE_ID;
        $consentEntityIdField = ConsentStore::CONSENT_ENTITY_ID;
        $this->getSelect()->joinLeft(
            [$consentStoreTableName => $this->getTable($consentStoreTableName)],
            "(main_table.{$consentId} = {$consentStoreTableName}.{$consentEntityIdField}"
            . " AND {$consentStoreTableName}.{$storeIdField} = {$storeId})",
            []
        );
        $this->join(
            ["{$consentStoreTableName}_default" => $this->getTable($consentStoreTableName)],
            "main_table.{$consentId} = {$consentStoreTableName}_default.{$consentEntityIdField}",
            []
        );
        $this->addFieldToFilter(
            "{$consentStoreTableName}_default.{$storeIdField}",
            Store::DEFAULT_STORE_ID
        );
        $this->storeDataAdded = true;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return \Zend_Db_Expr
     */
    private function getZendExpressionForField(string $field)
    {
        $field = $this->getConnection()->quoteIdentifier($field);
        $consentStoreTableName = ResourceConsentStore::TABLE_NAME;
        $defaultStoreField = "{$consentStoreTableName}.{$field}";
        $currentStoreField = "{$consentStoreTableName}_default.{$field}";

        return new \Zend_Db_Expr('IFNULL(' . $defaultStoreField . ', ' . $currentStoreField . ')');
    }
}
