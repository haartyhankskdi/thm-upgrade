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

namespace Mageplaza\RewardPointsUltimate\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ReferralInterface
 * @package Mageplaza\RewardPointsUltimate\Api\Data
 */
interface ReferralInterface extends ExtensibleDataInterface
{
    const CUSTOMER_ACTION            = 'customer_action';
    const CUSTOMER_POINTS            = 'customer_points';
    const CUSTOMER_MONEY_STEP        = 'customer_money_step';
    const CUSTOMER_DISCOUNT          = 'customer_discount';
    const CUSTOMER_APPLY_TO_SHIPPING = 'customer_apply_to_shipping';
    const REFERRAL_TYPE              = 'referral_type';
    const REFERRAL_POINTS            = 'referral_points';
    const REFERRAL_MONEY_STEP        = 'referral_money_step';
    const REFERRAL_APPLY_TO_SHIPPING = 'referral_apply_to_shipping';
    const REFERRAL_GROUP_IDS         = 'referral_group_ids';

    /**
     * @return int
     */
    public function getRuleId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setRuleId($value);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setName($value);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDescription($value);

    /**
     * @return string
     */
    public function getFromDate();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFromDate($value);

    /**
     * @return string
     */
    public function getToDate();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setToDate($value);

    /**
     * @return int
     */
    public function getIsActive();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setIsActive($value);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setSortOrder($value);

    /**
     * @return string
     */
    public function getConditionsSerialized();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setConditionsSerialized($value);

    /**
     * @return string
     */
    public function getActionsSerialized();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setActionsSerialized($value);

    /**
     * @return int
     */
    public function getStopRulesProcessing();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setStopRulesProcessing($value);

    /**
     * @return string
     */
    public function getCustomerAction();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCustomerAction($value);

    /**
     * @return int
     */
    public function getCustomerPoints();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCustomerPoints($value);

    /**
     * @return int
     */
    public function getCustomerMoneyStep();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCustomerMoneyStep($value);

    /**
     * @return int
     */
    public function getCustomerDiscount();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCustomerDiscount($value);

    /**
     * @return int
     */
    public function getCustomerApplyToShipping();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCustomerApplyToShipping($value);

    /**
     * @return string
     */
    public function getReferralType();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setReferralType($value);

    /**
     * @return int
     */
    public function getReferralPoints();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setReferralPoints($value);

    /**
     * @return int
     */
    public function getReferralMoneyStep();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setReferralMoneyStep($value);

    /**
     * @return int
     */
    public function getReferralApplyToShipping();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setReferralApplyToShipping($value);

    /**
     * @return int[]
     */
    public function getWebsiteIds();

    /**
     * @param int[] $value
     *
     * @return $this
     */
    public function setWebsiteIds(array $value);

    /**
     * @return int[]
     */
    public function getCustomerGroupIds();

    /**
     * @param int[] $value
     *
     * @return $this
     */
    public function setCustomerGroupIds(array $value);

    /**
     * @return int[]
     */
    public function getReferralGroupIds();

    /**
     * @param int[] $value
     *
     * @return $this
     */
    public function setReferralGroupIds($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\ReferralExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Mageplaza\RewardPointsUltimate\Api\Data\ReferralExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Mageplaza\RewardPointsUltimate\Api\Data\ReferralExtensionInterface $extensionAttributes
    );
}
