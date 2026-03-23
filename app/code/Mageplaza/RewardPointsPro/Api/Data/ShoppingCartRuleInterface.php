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

/**
 * Interface ShoppingCartRuleInterface
 * @package Mageplaza\RewardPointsPro\Api\Data
 */
interface ShoppingCartRuleInterface extends RuleInterface
{
    const RULE_TYPE         = 'rule_type';
    const POINT_AMOUNT      = 'point_amount';
    const MONEY_STEP        = 'money_step';
    const QTY_STEP          = 'qty_step';
    const MAX_POINTS        = 'max_points';
    const DISCOUNT_STYLE    = 'discount_style';
    const DISCOUNT_AMOUNT   = 'discount_amount';
    const APPLY_TO_SHIPPING = 'apply_to_shipping';

    /**
     * @return int
     */
    public function getRuleType();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setRuleType($value);

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
    public function getMoneyStep();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMoneyStep($value);

    /**
     * @return int
     */
    public function getQtyStep();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setQtyStep($value);

    /**
     * @return int
     */
    public function getMaxPoints();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMaxPoints($value);

    /**
     * @return string
     */
    public function getDiscountStyle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDiscountStyle($value);

    /**
     * @return int
     */
    public function getDiscountAmount();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setDiscountAmount($value);

    /**
     * @return int
     */
    public function getApplyToShipping();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setApplyToShipping($value);
}
