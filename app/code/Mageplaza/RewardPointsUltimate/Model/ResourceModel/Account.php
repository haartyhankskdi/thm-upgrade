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

namespace Mageplaza\RewardPointsUltimate\Model\ResourceModel;

use Mageplaza\RewardPoints\Model\ResourceModel\Account as StandardAccount;
use Mageplaza\RewardPoints\Model\Source\Status;
use Zend_Db_Expr;

/**
 * Class Account
 * @package Mageplaza\RewardPointsUltimate\Model\ResourceModel
 */
class Account extends StandardAccount
{
    /**
     * @param $account
     * @param $actionCode
     * @param $period
     *
     * @return string
     */
    public function getMilestoneTotalPoints($account, $actionCode, $period)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from(
                $this->getTable('mageplaza_reward_transaction'),
                ['total_points' => new Zend_Db_Expr('sum(point_amount)')]
            )
            ->where('status = ?', Status::COMPLETED)
            ->where('reward_id = ?', $account->getId())
            ->where('action_code IN (?)', explode(',', $actionCode));

        if (!empty($period)) {
            $select->where('created_at > ?', $period);
        }

        return $connection->fetchOne($select);
    }

    /**
     * @param $customerId
     *
     * @return int
     */
    public function getTotalOrder($customerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('sales_order'), 'COUNT(*)')
            ->where('customer_id=?', $customerId);

        return (int)$connection->fetchOne($select);
    }
}
