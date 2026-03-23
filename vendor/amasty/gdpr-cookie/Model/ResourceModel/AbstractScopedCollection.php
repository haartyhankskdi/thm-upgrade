<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\ResourceModel;

use Amasty\GdprCookie\Model\StoreData\ScopedFieldsProvider;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

abstract class AbstractScopedCollection extends AbstractCollection
{
    public const STORE_ALIAS = 'store_table';

    /**
     * @var int
     */
    protected $storeId = 0;

    /**
     * @var array
     */
    protected $scopedFields;

    /**
     * @var array
     */
    protected $mainTableFields;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        ScopedFieldsProvider $scopedFieldsProvider,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->scopedFields = $scopedFieldsProvider->getScopedFields($this->getMainTable());
        $this->mainTableFields = array_keys($this->getConnection()->describeTable($this->getMainTable()));
    }

    public function setStoreId(int $storeId)
    {
        $this->storeId = $storeId;
        $this->addStoreData($storeId);

        return $this;
    }

    abstract protected function addStoreData(int $storeId);

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return AbstractCollection
     */
    public function addFieldToSelect($field, $alias = null)
    {
        if (is_string($field)
            && $this->storeId
            && in_array($field, $this->scopedFields)
        ) {
            $field = $this->getZendExpressionForField($field);
        }

        return parent::addFieldToSelect($field, $alias);
    }

    /**
     * Add field filter to collection
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @return AbstractCollection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_string($field)
            && $this->storeId
            && in_array($field, $this->scopedFields)
        ) {
            $field = $this->getZendExpressionForField($field);
        } elseif (in_array($field, $this->mainTableFields)) {
            $field = 'main_table.' . $field;
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @param string $field
     * @return \Zend_Db_Expr
     */
    protected function getZendExpressionForField(string $field)
    {
        $field = $this->getConnection()->quoteIdentifier($field);

        return new \Zend_Db_Expr('IFNULL(' . self::STORE_ALIAS . '.' . $field . ', main_table.' . $field . ')');
    }
}
