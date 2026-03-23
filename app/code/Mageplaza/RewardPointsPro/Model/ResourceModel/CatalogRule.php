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

/**
 * Class CatalogRule
 * @package Mageplaza\RewardPointsPro\Model\ResourceModel
 */
class CatalogRule extends Rules
{
    /**
     * @var string
     */
    protected $associatedEntityMapVirtual
        = 'Mageplaza\RewardPointsPro\Model\ResourceModel\CatalogRule\AssociatedEntityMap';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_reward_catalogrule', 'rule_id');
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $productId
     *
     * @return array
     */
    public function getRulesFromProduct($date, $websiteId, $customerGroupId, $productId)
    {
        $connection = $this->getConnection();
        if (is_string($date)) {
            $date = strtotime($date);
        }
        $select = $connection->select()
            ->from($this->getTable('mageplaza_reward_catalogrule_product'))
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id = ?', $productId)
            ->where('from_time = 0 or from_time < ?', $date)
            ->where('to_time = 0 or to_time > ?', $date)
            ->order('sort_order ASC');

        return $connection->fetchAll($select);
    }
}
