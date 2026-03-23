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

namespace Mageplaza\RewardPointsPro\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface RuleInterface
 * @package Mageplaza\RewardPointsPro\Api\Data
 */
interface RuleInterface extends ExtensibleDataInterface
{
    const RULE_ID               = 'rule_id';
    const NAME                  = 'name';
    const DESCRIPTION           = 'description';
    const FROM_DATE             = 'from_date';
    const TO_DATE               = 'to_date';
    const IS_ACTIVE             = 'is_active';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const ACTIONS_SERIALIZED    = 'actions_serialized';
    const STOP_RULES_PROCESSING = 'stop_rules_processing';
    const SORT_ORDER            = 'sort_order';
    const ACTION                = 'action';
    const WEBSITE_IDS           = 'website_ids';
    const CUSTOMER_GROUP_IDS    = 'customer_group_ids';
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
    public function getAction();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setAction($value);

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
}
