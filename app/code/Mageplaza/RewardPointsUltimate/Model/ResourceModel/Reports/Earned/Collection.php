<?php
/**
 * /**
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

namespace Mageplaza\RewardPointsUltimate\Model\ResourceModel\Reports\Earned;

use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\ResourceModel\Reports\ReportsCollection;
use Zend_Db_Expr;

/**
 * Class AbstractCollection
 * @package Mageplaza\ReportsPro\Model\ResourceModel\Grid
 */
class Collection extends ReportsCollection
{
    /**
     * @return $this
     */
    public function joinTable()
    {
        $this->getSelect()->join(
            ['customer' => $this->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            ['group_id']
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function addMapValue()
    {
        $this->addFilterToMap('store_id', 'main_table.store_id')
            ->addFilterToMap('group_id', 'customer.group_id')
            ->addFilterToMap('created_at', 'main_table.created_at');

        return $this;
    }

    /**
     * @return array
     */
    public function addExtraSelectedColumns()
    {
        $excludeAction = [
            Data::ACTION_SPENDING_ORDER,
            Data::ACTION_UNLIKE_FACEBOOK,
            Data::ACTION_SPENDING_REFUND,
            Data::ACTION_SELL_POINTS,
            Data::ACTION_SELL_POINTS_REFUND,
            Data::ACTION_REFERRAL_REFUND
        ];
        $this->_selectedColumns['total_earned'] = new Zend_Db_Expr('SUM(main_table.point_amount)');
        foreach ($this->transactionActions->toOptionArray() as $action) {
            if (in_array($action['value'], $excludeAction)) {
                continue;
            }

            $this->_selectedColumns[$action['value']] = new Zend_Db_Expr(
                sprintf(
                    'SUM(CASE WHEN main_table.action_code = \'%s\' THEN main_table.point_amount ELSE 0 END )',
                    $action['value']
                )
            );
        }

        return $this->_selectedColumns;
    }
}
