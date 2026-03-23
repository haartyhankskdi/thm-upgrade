<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Model\ResourceModel;

use Exception;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection as RuleAbstractCollection;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractCollection
 * @package Mageplaza\RewardPointsPro\Model\ResourceModel
 */
abstract class AbstractCollection extends RuleAbstractCollection
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap;

    /**
     * @var TimezoneInterface
     */
    protected $_date;

    /**
     * @var SearchCriteriaInterface
     */
    protected $searchCriteria;

    /**
     * @var string
     */
    protected $associatedEntityMapVirtual
        = 'Mageplaza\RewardPointsPro\Model\ResourceModel\ShoppingCart\AssociatedEntityMap';

    /**
     * AbstractCollection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param TimezoneInterface $date
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        TimezoneInterface $date,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

        $this->_associatedEntitiesMap = $this->getAssociatedEntitiesMap();
        $this->_date                  = $date;
    }

    /**
     * @param string $entityType
     * @param string $objectField
     *
     * @return void
     * @throws LocalizedException
     */
    protected function mapAssociatedEntities($entityType, $objectField)
    {
        if (!$this->_items) {
            return;
        }

        $entityInfo  = $this->_getAssociatedEntityInfo($entityType);
        $ruleIdField = $entityInfo['rule_id_field'];
        $entityIds   = $this->getColumnValues($ruleIdField);

        $select = $this->getConnection()->select()->from(
            $this->getTable($entityInfo['associations_table'])
        )->where(
            $ruleIdField . ' IN (?)',
            $entityIds
        );

        $associatedEntities = $this->getConnection()->fetchAll($select);

        array_map(function ($associatedEntity) use ($entityInfo, $ruleIdField, $objectField) {
            $item                  = $this->getItemByColumnValue($ruleIdField, $associatedEntity[$ruleIdField]);
            $itemAssociatedValue   = $item->getData($objectField) === null ? [] : $item->getData($objectField);
            $itemAssociatedValue[] = $associatedEntity[$entityInfo['entity_id_field']];
            $item->setData($objectField, $itemAssociatedValue);
        }, $associatedEntities);
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _afterLoad()
    {
        $this->mapAssociatedEntities('website', 'website_ids');
        $this->mapAssociatedEntities('customer_group', 'customer_group_ids');
        $this->setFlag('add_websites_to_result', false);

        return parent::_afterLoad();
    }

    /**
     * Provide support for customer group id filter
     *
     * @param string $field
     * @param null $condition
     *
     * @return $this|AbstractCollection
     * @throws LocalizedException
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'customer_group_ids' || $field === ['customer_group_ids']) {
            return $this->addCustomerGroupFilter($condition);
        }
        if ($field === ['website_ids']) {
            return $this->addWebsiteFilter($condition);
        }

        parent::addFieldToFilter($field, $condition);

        return $this;
    }

    /**
     * @param $customerGroupId
     * @param $websiteId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function setValidationFilter($customerGroupId, $websiteId)
    {
        $now = $this->_date->date()->format('Y-m-d');
        $this->addWebsiteFilter($websiteId);
        $this->addCustomerGroupFilter($customerGroupId);
        $this->getSelect()->where(
            'from_date is null or from_date <= ?',
            $now
        )->where(
            'to_date is null or to_date >= ?',
            $now
        );
        $this->setOrder('sort_order', self::SORT_ORDER_ASC);

        return $this;
    }

    /**
     * @param $customerGroupId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function addCustomerGroupFilter($customerGroupId)
    {
        $entityInfo = $this->_getAssociatedEntityInfo('customer_group');
        if (!$this->getFlag('is_customer_group_joined')) {
            $this->setFlag('is_customer_group_joined', true);
            $this->getSelect()->join(
                ['customer_group' => $this->getTable($entityInfo['associations_table'])],
                $this->getConnection()
                    ->quoteInto('customer_group.' . $entityInfo['entity_id_field'] . ' = ?', $customerGroupId)
                . ' AND main_table.' . $entityInfo['rule_id_field'] . ' = customer_group.'
                . $entityInfo['rule_id_field'],
                []
            );
        }

        return $this;
    }

    /**
     * @return array
     */
    private function getAssociatedEntitiesMap()
    {
        if (!$this->_associatedEntitiesMap) {
            $this->_associatedEntitiesMap = ObjectManager::getInstance()
                ->get($this->associatedEntityMapVirtual)
                ->getData();
        }

        return $this->_associatedEntitiesMap;
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * Set search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     */
    public function setSearchCriteria(?SearchCriteriaInterface $searchCriteria = null)
    {
        $this->searchCriteria = $searchCriteria;

        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param ExtensibleDataInterface[] $items
     *
     * @return $this
     */
    public function setItems(?array $items = null)
    {
        if (!$items) {
            return $this;
        }
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }
}
