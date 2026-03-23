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

namespace Mageplaza\RewardPointsPro\Model;

use Mageplaza\RewardPointsPro\Api\Data\SCEarningRuleExtensionInterface;
use Mageplaza\RewardPointsPro\Api\Data\SCEarningRuleInterface;
use Mageplaza\RewardPointsPro\Model\ResourceModel\ShoppingCartEarningRule as ShoppingCartEarningRuleResourceModel;

/**
 * Class ShoppingCartEarningRule
 * @package Mageplaza\RewardPointsPro\Model
 */
class ShoppingCartEarningRule extends Rules implements SCEarningRuleInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_init(ShoppingCartEarningRuleResourceModel::class);
        $this->setIdFieldName('rule_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleType()
    {
        return $this->getData(self::RULE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleType($value)
    {
        return $this->setData(self::RULE_TYPE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointAmount()
    {
        return $this->getData(self::POINT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointAmount($value)
    {
        return $this->setData(self::POINT_AMOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMoneyStep()
    {
        return $this->getData(self::MONEY_STEP);
    }

    /**
     * {@inheritdoc}
     */
    public function setMoneyStep($value)
    {
        return $this->setData(self::MONEY_STEP, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getQtyStep()
    {
        return $this->getData(self::QTY_STEP);
    }

    /**
     * {@inheritdoc}
     */
    public function setQtyStep($value)
    {
        return $this->setData(self::QTY_STEP, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPoints()
    {
        return $this->getData(self::MAX_POINTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxPoints($value)
    {
        return $this->setData(self::MAX_POINTS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountStyle()
    {
        return $this->getData(self::DISCOUNT_STYLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountStyle($value)
    {
        return $this->setData(self::DISCOUNT_STYLE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountAmount()
    {
        return $this->getData(self::DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountAmount($value)
    {
        return $this->setData(self::DISCOUNT_AMOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyToShipping()
    {
        return $this->getData(self::APPLY_TO_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setApplyToShipping($value)
    {
        return $this->setData(self::APPLY_TO_SHIPPING, $value);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return SCEarningRuleExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param SCEarningRuleExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        SCEarningRuleExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
