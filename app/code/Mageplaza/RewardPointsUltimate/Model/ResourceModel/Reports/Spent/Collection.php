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

namespace Mageplaza\RewardPointsUltimate\Model\ResourceModel\Reports\Spent;

use Mageplaza\RewardPointsUltimate\Model\ResourceModel\Reports\ReportsCollection;
use Zend_Db_Expr;

/**
 * Class Collection
 * @package Mageplaza\RewardPointsUltimate\Model\ResourceModel\Reports\Spent
 */
class Collection extends ReportsCollection
{
    /**
     * @var bool
     */
    protected $isEarned = false;

    /**
     * @return array|void
     */
    public function addExtraSelectedColumns()
    {
        $fields = [
            ['key' => 'total_spent', 'value' => 'mp_reward_spent'],
            ['key' => 'total_reward_discount', 'value' => 'mp_reward_discount'],
            ['key' => 'total_grand_total', 'value' => 'grand_total']
        ];
        foreach ($fields as $field) {
            $this->_selectedColumns[$field['key']] = new Zend_Db_Expr(
                sprintf('SUM( main_table.%s)', $field['value'])
            );
        }
        $this->_selectedColumns['number_of_orders'] = new Zend_Db_Expr('COUNT(*)');
        $this->getSelect()->where('main_table.mp_reward_spent > 0');
    }
}
