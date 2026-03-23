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

namespace Mageplaza\RewardPointsUltimate\Model\ResourceModel\Referral;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPointsPro\Model\ResourceModel\AbstractCollection;
use Mageplaza\RewardPointsUltimate\Api\Data\ReferralSearchResultInterface;
use Mageplaza\RewardPointsUltimate\Model\Referral;

/**
 * Class Collection
 * @package Mageplaza\RewardPointsUltimate\Model\ResourceModel\Referral
 */
class Collection extends AbstractCollection implements ReferralSearchResultInterface
{
    /**
     * @type string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * @var string
     */
    protected $associatedEntityMapVirtual
        = 'Mageplaza\RewardPointsUltimate\Model\ResourceModel\Referral\AssociatedEntityMap';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(
            Referral::class,
            \Mageplaza\RewardPointsUltimate\Model\ResourceModel\Referral::class
        );
    }

    /**
     * @param string $field
     * @param null $condition
     *
     * @return $this|AbstractCollection|Collection
     * @throws LocalizedException
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'referral_group_ids' || $field === ['referral_group_ids']) {
            return $this->addReferralGroupFilter($condition);
        }

        parent::addFieldToFilter($field, $condition);

        return $this;
    }

    /**
     * @param $customerGroupId
     * @param $websiteId
     * @param string $referGroupId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function setValidationFilter($customerGroupId, $websiteId, $referGroupId = '')
    {
        $this->addReferralGroupFilter($referGroupId);

        return parent::setValidationFilter($customerGroupId, $websiteId);
    }

    /**
     * @param $referGroupId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function addReferralGroupFilter($referGroupId)
    {
        $entityInfo = $this->_getAssociatedEntityInfo('referral_group');
        if (!$this->getFlag('is_referral_group_joined')) {
            $this->setFlag('is_referral_group_joined', true);
            if ($referGroupId) {
                $this->getSelect()->join(
                    ['referral_group' => $this->getTable($entityInfo['associations_table'])],
                    $this->getConnection()
                        ->quoteInto('referral_group.' . $entityInfo['entity_id_field'] . ' = ?', $referGroupId)
                    . ' AND main_table.' . $entityInfo['rule_id_field'] . ' = referral_group.'
                    . $entityInfo['rule_id_field'],
                    []
                );
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws Exception
     */
    protected function _afterLoad()
    {
        $this->mapAssociatedEntities('referral_group', 'referral_group_ids');

        return parent::_afterLoad();
    }
}
