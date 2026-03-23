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

/**
 * Class Referral
 * @package Mageplaza\RewardPointsUltimate\Model\ResourceModel
 */
class Referral extends Rules
{
    /**
     * @var string
     */
    protected $associatedEntityMapVirtual
        = 'Mageplaza\RewardPointsUltimate\Model\ResourceModel\Referral\AssociatedEntityMap';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('mageplaza_reward_refer', 'rule_id');
    }

    /**
     * Retrieve referral group ids of specified rule
     *
     * @param int $ruleId
     *
     * @return array
     */
    public function getReferralGroupIds($ruleId)
    {
        return $this->getAssociatedEntityIds($ruleId, 'referral_group');
    }
}
