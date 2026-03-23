<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel\DeleteRequest\Grid;

use Amasty\Gdpr\Model\DeleteRequest;

class Collection extends \Amasty\Gdpr\Model\ResourceModel\Grid\AbstractSearchResult
{
    /**
     * @var array
     */
    protected $_map = ['fields' => ['created_at' => 'main_table.created_at']];

    /**
     * Init collection select
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _initSelect()
    {
        parent::_initSelect();

        $this->joinCustomerData();
        $this->getSelect()->where('main_table.approved != ?', DeleteRequest::IS_APPROVED);
        $this->getSelect()->group('main_table.id');
        $this->addFilterToMap(
            'name',
            new \Zend_Db_Expr("CONCAT_WS(' ', prefix, firstname, middlename, lastname, suffix)")
        );

        $this
            ->joinOrderStatuses(['complete'])
            ->joinOrderStatuses(['pending', 'pending_payment']);

        return $this;
    }

    /**
     * @param $statuses
     *
     * @return $this
     */
    protected function joinOrderStatuses($statuses)
    {
        $alias = $statuses[0];

        $joinCondition = $this->getConnection()->quoteInto(
            "{$alias}_order.customer_id = main_table.customer_id AND {$alias}_order.status IN (?)",
            $statuses
        );

        $this->getSelect()
            ->joinLeft(
                ["{$alias}_order" => $this->getTable('sales_order')],
                $joinCondition,
                ["{$alias}_qty" => "COUNT(DISTINCT {$alias}_order.entity_id)"]
            );

        return $this;
    }
}
