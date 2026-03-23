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

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Milestone
 * @package Mageplaza\RewardPointsUltimate\Model\ResourceModel
 */
class Milestone extends AbstractDb
{
    /**
     * @var string
     */
    protected $_tierCustomerTable;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('mageplaza_reward_milestone', 'tier_id');
    }

    /**
     * Milestone constructor.
     *
     * @param Context $context
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_tierCustomerTable = $this->getTable('mageplaza_reward_milestone_customer');
    }

    /**
     * @param \Mageplaza\RewardPointsUltimate\Model\Milestone $tier
     *
     * @return array
     */
    public function getCustomerIds(\Mageplaza\RewardPointsUltimate\Model\Milestone $tier)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->
        from(
            $this->_tierCustomerTable,
            'customer_id'
        )
            ->where(
                'tier_id = ?',
                (int)$tier->getId()
            );

        return $adapter->fetchCol($select);
    }

    /**
     * @param \Mageplaza\RewardPointsUltimate\Model\Milestone $tier
     * @param $customerId
     *
     * @return $this
     */
    public function loadByCustomerId(\Mageplaza\RewardPointsUltimate\Model\Milestone $tier, $customerId)
    {
        $connection = $this->getConnection();
        $bind = ['customer_id' => $customerId];
        $select = $connection->select()->from(
            $this->_tierCustomerTable,
            ['tier_id']
        )->where(
            'customer_id = :customer_id'
        );

        $tierId = $connection->fetchOne($select, $bind);
        if ($customerId) {
            $this->load($tier, $tierId);
        } else {
            $tier->setData([]);
        }

        return $this;
    }

    /**
     * @param \Mageplaza\RewardPointsUltimate\Model\Milestone $upTier
     * @param int $customerId
     */
    public function upTier($upTier, $customerId)
    {
        $where = ['customer_id = ?' => $customerId];
        $bind = ['tier_id' => (int)$upTier->getId()];
        $adapter = $this->getConnection();
        $adapter->update($this->_tierCustomerTable, $bind, $where);
    }

    /**
     * @param \Mageplaza\RewardPointsUltimate\Model\Milestone $tier
     * @param int $customerId
     */
    public function addTier($tier, $customerId)
    {
        $bind = ['tier_id' => (int)$tier->getId(), 'customer_id' => (int)$customerId];
        $adapter = $this->getConnection();
        $adapter->insert($this->_tierCustomerTable, $bind);
    }

    /**
     * @param \Mageplaza\RewardPointsUltimate\Model\Milestone $tier
     * @param int $customerId
     */
    public function deleteTier($tier, $customerId)
    {
        $where = ['customer_id = ?' => $customerId, 'tier_id = ?' => $tier->getId()];
        $adapter = $this->getConnection();
        $adapter->delete($this->_tierCustomerTable, $where);
    }
}
