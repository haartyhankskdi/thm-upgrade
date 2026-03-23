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
 * Interface BehaviorInterface
 * @package Mageplaza\RewardPointsUltimate\Api\Data
 */
interface BehaviorInterface extends ExtensibleDataInterface
{
    const RULE_ID            = 'rule_id';
    const NAME               = 'name';
    const DESCRIPTION        = 'description';
    const FROM_DATE          = 'from_date';
    const TO_DATE            = 'to_date';
    const IS_ACTIVE          = 'is_active';
    const SORT_ORDER         = 'sort_order';
    const POINT_ACTION       = 'point_action';
    const MIN_WORDS          = 'min_words';
    const MIN_GRAND_TOTAL    = 'min_grand_total';
    const MIN_DAYS           = 'min_days';
    const IS_PURCHASED       = 'is_purchased';
    const IS_LOOP            = 'is_loop';
    const IS_ENABLED_EMAIL   = 'is_enabled_email';
    const SENDER             = 'sender';
    const EMAIL_TEMPLATE     = 'email_template';
    const MIN_INTERVAL       = 'min_interval';
    const ACTION             = 'action';
    const FB_APP_ID          = 'fb_app_id';
    const POINT_AMOUNT       = 'point_amount';
    const MAX_POINT          = 'max_point';
    const MAX_POINT_PERIOD   = 'max_point_period';
    const WEBSITE_IDS        = 'website_ids';
    const CUSTOMER_GROUP_IDS = 'customer_group_ids';

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
    public function getPointAction();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setPointAction($value);

    /**
     * @return int
     */
    public function getMinWords();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMinWords($value);

    /**
     * @return int
     */
    public function getMinDays();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMinDays($value);

    /**
     * @return int
     */
    public function getIsPurchased();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setIsPurchased($value);

    /**
     * @return int
     */
    public function getIsLoop();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setIsLoop($value);

    /**
     * @return int
     */
    public function getIsEnabledEmail();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setIsEnabledEmail($value);

    /**
     * @return string
     */
    public function getSender();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setSender($value);

    /**
     * @return string
     */
    public function getEmailTemplate();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setEmailTemplate($value);

    /**
     * @return int
     */
    public function getMinInterval();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMinInterval($value);

    /**
     * @return string
     */
    public function getAction();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setAction($value);

    /**
     * @return string
     */
    public function getFbAppId();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFbAppId($value);

    /**
     * @return int
     */
    public function getPointAmount();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPointAmount($value);

    /**
     * @return int
     */
    public function getMaxPoint();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMaxPoint($value);

    /**
     * @return string
     */
    public function getMaxPointPeriod();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMaxPointPeriod($value);

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
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\BehaviorExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Mageplaza\RewardPointsUltimate\Api\Data\BehaviorExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Mageplaza\RewardPointsUltimate\Api\Data\BehaviorExtensionInterface $extensionAttributes
    );
}
