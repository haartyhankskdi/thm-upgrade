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
use Magento\Framework\Model\AbstractModel;

/**
 * Class ShoppingCartSpendingRule
 * @package Mageplaza\RewardPointsPro\Model\ResourceModel
 */
class ShoppingCartSpendingRule extends Rules
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_reward_shopping_cart', 'rule_id');
    }

    /**
     * Save rule's associated store labels.
     *
     * @param AbstractModel $object
     *
     * @return $this
     * @throws Exception
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasStoreLabels()) {
            $this->saveStoreLabels($object->getId(), $object->getStoreLabels());
        }

        return parent::_afterSave($object);
    }

    /**
     * Get all existing rule labels
     *
     * @param int $ruleId
     *
     * @return array
     */
    public function getStoreLabels($ruleId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('mageplaza_reward_shopping_cart_label'),
            ['store_id', 'label']
        )->where(
            'rule_id = :rule_id'
        );

        return $this->getConnection()->fetchPairs($select, [':rule_id' => $ruleId]);
    }

    /**
     * Save rule labels for different store views
     *
     * @param int $ruleId
     * @param array $labels
     *
     * @return $this
     * @throws Exception
     */
    public function saveStoreLabels($ruleId, $labels)
    {
        $deleteByStoreIds = [];
        $table = $this->getTable('mageplaza_reward_shopping_cart_label');
        $connection = $this->getConnection();

        $data = [];
        foreach ($labels as $storeId => $label) {
            if (iconv_strlen($label, 'UTF-8')) {
                $data[] = ['rule_id' => $ruleId, 'store_id' => $storeId, 'label' => $label];
            } else {
                $deleteByStoreIds[] = $storeId;
            }
        }

        $connection->beginTransaction();
        try {
            if (!empty($data)) {
                $connection->insertOnDuplicate($table, $data, ['label']);
            }

            if (!empty($deleteByStoreIds)) {
                $connection->delete($table, ['rule_id=?' => $ruleId, 'store_id IN (?)' => $deleteByStoreIds]);
            }
        } catch (Exception $e) {
            $connection->rollback();
            throw $e;
        }
        $connection->commit();

        return $this;
    }
}
