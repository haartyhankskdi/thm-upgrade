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

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPointsPro\Api\Data\SCSpendingRuleExtensionInterface;
use Mageplaza\RewardPointsPro\Api\Data\SCSpendingRuleInterface;
use Mageplaza\RewardPointsPro\Model\ResourceModel\ShoppingCartSpendingRule as ShoppingCartSpendingRuleResourceModel;

/**
 * Class ShoppingCartSpendingRule
 * @package Mageplaza\RewardPointsPro\Model
 */
class ShoppingCartSpendingRule extends Rules implements SCSpendingRuleInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_init(ShoppingCartSpendingRuleResourceModel::class);
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get Rule label by specified store
     *
     * @param null $store
     *
     * @return bool|mixed
     * @throws LocalizedException
     */
    public function getStoreLabel($store = null)
    {
        $storeId = $this->storeManager->getStore($store)->getId();
        $labels  = (array) $this->getStoreLabels();

        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        }

        if (isset($labels[0]) && $labels[0]) {
            return $labels[0];
        }

        return false;
    }

    /**
     * Set if not yet and retrieve rule store labels
     * @return mixed
     * @throws LocalizedException
     */
    public function getStoreLabels()
    {
        if (!$this->hasStoreLabels()) {
            $labels = $this->_getResource()->getStoreLabels($this->getId());

            $this->setStoreLabels($labels);
        }

        return $this->_getData(self::LABELS);
    }

    /**
     * @param $storeId
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getLabelByStoreId($storeId)
    {
        $storeLabels = $this->getStoreLabels();
        if (isset($storeLabels[$storeId]) && trim($storeLabels[$storeId])) {
            return $storeLabels[$storeId];
        }

        if (isset($storeLabels[0]) && trim($storeLabels[0])) {
            return $storeLabels[0];
        }

        return $this->getName();
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
    public function getLabels()
    {
        if (!$this->hasStoreLabels()) {
            $labels = $this->_getResource()->getStoreLabels($this->getId());
            $result = [];
            foreach ($labels as $key => $label) {
                $result[] = [
                    'store_id' => $key,
                    'label'    => $label
                ];
            }

            $this->setLabels($result);
        }

        return $this->getData(self::LABELS);
    }

    /**
     * {@inheritdoc}
     */
    public function setLabels(?array $storeLabels = null)
    {
        return $this->setData(self::LABELS, $storeLabels);
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
     * @return SCSpendingRuleExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param SCSpendingRuleExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        SCSpendingRuleExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
