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

namespace Mageplaza\RewardPointsPro\Model\Rule\Condition;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Product\AbstractProduct;
use Magento\Store\Model\Store;

/**
 * Class Product
 * @package Mageplaza\RewardPointsPro\Model\Rule\Condition
 */
class Product extends AbstractProduct
{
    /**
     * Validate product attribute value for condition
     *
     * @param \Magento\Catalog\Model\Product|AbstractModel $model
     *
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        $attrCode = $this->getAttribute();
        if ('category_ids' == $attrCode) {
            return $this->validateAttribute($model->getAvailableInCategories());
        }

        $oldAttrValue = $model->getData($attrCode);
        if ($oldAttrValue === null) {
            return false;
        }

        $this->_setAttributeValue($model);
        $result = $this->validateAttribute($model->getData($attrCode));
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
    }

    /**
     * Restore old attribute value
     *
     * @param AbstractModel $model
     * @param mixed $oldAttrValue
     *
     * @return void
     */
    protected function _restoreOldAttrValue(AbstractModel $model, $oldAttrValue)
    {
        $attrCode = $this->getAttribute();
        if ($oldAttrValue === null) {
            $model->unsetData($attrCode);
        } else {
            $model->setData($attrCode, $oldAttrValue);
        }
    }

    /**
     * Set attribute value
     *
     * @param \Magento\Catalog\Model\Product|AbstractModel $model
     *
     * @return $this
     */
    protected function _setAttributeValue(AbstractModel $model)
    {
        $storeId = $model->getStoreId();
        $defaultStoreId = Store::DEFAULT_STORE_ID;

        if (!isset($this->_entityAttributeValues[$model->getId()])) {
            return $this;
        }

        $productValues = $this->_entityAttributeValues[$model->getId()];

        if (!isset($productValues[$storeId]) && !isset($productValues[$defaultStoreId])) {
            return $this;
        }

        $value = isset($productValues[$storeId]) ? $productValues[$storeId] : $productValues[$defaultStoreId];
        $value = $this->_prepareDatetimeValue($value, $model);
        $value = $this->_prepareMultiselectValue($value, $model);
        $model->setData($this->getAttribute(), $value);

        return $this;
    }

    /**
     * Prepare datetime attribute value
     *
     * @param mixed $value
     * @param \Magento\Catalog\Model\Product|AbstractModel $model
     *
     * @return mixed
     */
    protected function _prepareDatetimeValue($value, AbstractModel $model)
    {
        $attribute = $model->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getBackendType() == 'datetime') {
            $value = strtotime($value);
        }

        return $value;
    }

    /**
     * Prepare multiselect attribute value
     *
     * @param mixed $value
     * @param \Magento\Catalog\Model\Product|AbstractModel $model
     *
     * @return mixed
     */
    protected function _prepareMultiselectValue($value, AbstractModel $model)
    {
        $attribute = $model->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getFrontendInput() == 'multiselect') {
            $value = strlen($value) ? explode(',', $value) : [];
        }

        return $value;
    }
}
