<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model\ResourceModel\Reports;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Mageplaza\RewardPoints\Model\ActionFactory;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\Source\Period;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class ReportsCollection
 * @package Mageplaza\RewardPointsUltimate\Model\ResourceModel\Reports
 */
class ReportsCollection extends SearchResult implements SearchResultInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var array
     */
    protected $_selectedColumns = [];

    /**
     * @var ActionFactory
     */
    protected $transactionActions;

    /**
     * @var bool
     */
    protected $isEarned = true;

    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param RequestInterface $request
     * @param Data $helperData
     * @param ActionFactory $transactionActions
     * @param string $mainTable
     * @param string $resourceModel
     *
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        RequestInterface $request,
        Data $helperData,
        ActionFactory $transactionActions,
        $mainTable,
        $resourceModel
    ) {
        $this->_request           = $request;
        $this->_helperData        = $helperData;
        $this->transactionActions = $transactionActions;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->addMapValue();
        $this->joinTable();
        $this->getSelect()->from(['main_table' => $this->getMainTable()], $this->_getSelectedColumns());
        $filters = $this->getFilters();
        if (isset($filters['created_at'])) {
            $createAt = $filters['created_at'];
            if (isset($createAt['from'])) {
                $this->getSelect()->where('main_table.created_at >= ?', $this->formatDate($createAt['from']));
            }
            if (isset($createAt['to'])) {
                $this->getSelect()->where('main_table.created_at <= ?', $this->formatDate($createAt['to'], false));
            }
        }
        $customerGroupField = $this->getCustomerGroupField();
        if (isset($filters[$customerGroupField]) && $filters[$customerGroupField] != '32000') {
            $this->getSelect()->where($customerGroupField . '= ?', $filters[$customerGroupField]);
        }

        if (isset($filters['store_id']) && $filters['store_id']) {
            $this->getSelect()->where('main_table.store_id = ?', $filters['store_id']);
        }

        $this->getSelect()->group(['period']);

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerGroupField()
    {
        return $this->isEarned ? 'group_id' : 'customer_group_id';
    }

    /**
     * @return $this
     */
    public function joinTable()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function addMapValue()
    {
        return $this;
    }

    /**
     * @param string $date
     * @param bool $isFirstDay
     *
     * @return false|string
     */
    public function formatDate($date, $isFirstDay = true)
    {
        return date_format(date_create($date), 'Y-m-d' . ($isFirstDay ? ' 00:00:00' : ' 23:59:59'));
    }

    /**
     * Retrieve selected columns
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $connection = $this->getConnection();
        if (!$this->_selectedColumns) {
            $this->_selectedColumns = [
                'store_id'   => 'store_id',
                'created_at' => 'created_at',
                'period'     => sprintf(
                    '%s',
                    $connection->getDateFormatSql('main_table.created_at', $this->getPeriod())
                )
            ];
            $this->addExtraSelectedColumns();
        }

        return $this->_selectedColumns;
    }

    /**
     * @return array
     */
    public function addExtraSelectedColumns()
    {
        return $this->_selectedColumns;
    }

    /**
     * @return string
     */
    public function getPeriod()
    {
        $filters = $this->getFilters();
        $period  = '%Y-%m-%d';
        if (isset($filters['period'])) {
            switch ($filters['period']) {
                case Period::WEEK:
                    $period = '%Y-%u';
                    break;
                case Period::MONTH:
                    $period = '%Y-%m';
                    break;
                case Period::YEAR:
                    $period = '%Y';
                    break;
                default:
            }
        }

        return $period;
    }

    /**
     * @return mixed
     */
    public function getFilters()
    {
        $mpFilters = $this->_request->getParam('mpFilter', []);
        $filters   = $this->_request->getParam('filters', []);
        if (count($mpFilters) > 0) {
            $filters = $this->replaceValue($filters, $mpFilters);
        }

        return $filters;
    }

    /**
     * @param array $filters
     * @param array $mpFilter
     *
     * @return mixed
     */
    public function replaceValue($filters, $mpFilter)
    {
        $replaceData = [
            $this->getCustomerGroupField() => 'customer_group_id',
            'store_id'                     => 'store',
            'from'                         => 'startDate',
            'to'                           => 'endDate',
            'period'                       => 'period'
        ];

        foreach ($replaceData as $key => $value) {
            if (isset($mpFilter[$value])) {
                if ($key === 'from' || $key === 'to') {
                    $filters['created_at'][$key] = $mpFilter[$value];
                } else {
                    $filters[$key] = $mpFilter[$value];
                }
            }
        }

        return $filters;
    }

    /**
     * @return Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $select = clone $this->getSelect();
        $select->reset(Select::ORDER);

        return $this->getConnection()->select()->from($select, 'COUNT(*)');
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        return $this;
    }

    /**
     * @param string $field
     *
     * @return mixed
     */
    public function changeFieldName(&$field)
    {
        return $field;
    }
}
