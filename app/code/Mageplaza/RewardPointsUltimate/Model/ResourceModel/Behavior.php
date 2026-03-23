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

use Mageplaza\RewardPointsPro\Model\ResourceModel\Rules;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\Source\PointPeriod;
use Zend_Db_Expr;

/**
 * Class Behavior
 * @package Mageplaza\RewardPointsUltimate\Model\ResourceModel
 */
class Behavior extends Rules
{
    /**
     * @var string
     */
    protected $associatedEntityMapVirtual
        = 'Mageplaza\RewardPointsUltimate\Model\ResourceModel\Behavior\AssociatedEntityMap';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('mageplaza_reward_behavior', 'rule_id');
    }

    /**
     * @param $action
     * @param $behavior
     * @param $customerId
     *
     * @return int
     */
    public function checkMaxPoint($action, $behavior, $customerId)
    {
        $connection = $this->getConnection();
        $sql        = $connection->select()
            ->from(
                $this->getTable('mageplaza_reward_transaction'),
                ['total_point_amount' => new Zend_Db_Expr('SUM(point_amount)')]
            )->where('action_code =?', $action)
            ->where('customer_id =?', $customerId);

        if ($behavior->getMaxPointPeriod() && $behavior->getMaxPointPeriod() != PointPeriod::LIFETIME) {
            $period = strtoupper($behavior->getMaxPointPeriod());
            $sql->where('EXTRACT(' . $period . ' FROM `created_at`) = EXTRACT(' . $period . ' FROM UTC_TIMESTAMP())');
        }
        $result = $connection->fetchRow($sql);
        $point  = $behavior->getPointAmount();

        if (count($result) > 0) {
            if (isset($result['total_point_amount'])) {
                if ($result['total_point_amount'] >= $behavior->getMaxPoint()) {
                    $point = 0;
                } else {
                    if ($result['total_point_amount'] + $behavior->getPointAmount() > $behavior->getMaxPoint()) {
                        $point = $behavior->getMaxPoint() - $behavior->getPointAmount();
                    }
                }
            } else {
                if ($behavior->getPointAmount() > $behavior->getMaxPoint()) {
                    $point = $behavior->getMaxPoint();
                }
            }
        }

        return $point;
    }

    /**
     * @param $customerId
     *
     * @return bool
     */
    public function checkCustomerHasBirthday($customerId)
    {
        $connection = $this->getConnection();
        $sql        = $connection->select()
            ->from($this->getTable('mageplaza_reward_transaction'))
            ->where('action_code =?', Data::ACTION_CUSTOMER_BIRTHDAY)
            ->where('customer_id =?', $customerId)
            ->where('EXTRACT(YEAR FROM `created_at`) = EXTRACT(YEAR FROM UTC_TIMESTAMP())');
        $result     = $connection->fetchAll($sql);

        return (bool) $result;
    }
}
