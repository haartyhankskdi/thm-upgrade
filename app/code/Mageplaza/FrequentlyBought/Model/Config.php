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
 * @package     Mageplaza_FrequentlyBought
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FrequentlyBought\Model;

use Mageplaza\FrequentlyBought\Api\Data\ConfigInterface;

/**
 * Class Config
 * @package Mageplaza\FrequentlyBought\Model
 */
class Config extends \Magento\Framework\DataObject implements ConfigInterface
{
    const IS_ENABLE = 'is_enable';
    const PRODUCT_METHOD = 'product_method';
    const BLOCK_NAME = 'block_name';
    const ITEM_LIMIT = 'item_limit';
    const ENABLE_ADD_TO_WISHLIST = 'enable_add_to_wishlist';
    const REMOVE_RELATED_BLOCK = 'remove_related_block';
    const SEPARATOR_IMAGE = 'separator_image';
    const USE_POPUP = 'use_popup';

    /**
     * {@inheritdoc}
     */
    public function getIsEnable()
    {
        return $this->getData(self::IS_ENABLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsEnable($bool)
    {
        return $this->setData(self::IS_ENABLE, $bool);
    }

    public function getProductMethod()
    {
        return $this->getData(self::PRODUCT_METHOD);
    }

    public function setProductMethod($value)
    {
        return $this->setData(self::PRODUCT_METHOD, $value);
    }

    public function getBlockName()
    {
        return $this->getData(self::BLOCK_NAME);
    }

    public function setBlockName($value)
    {
        return $this->setData(self::BLOCK_NAME, $value);
    }

    public function getItemLimit()
    {
        return $this->getData(self::ITEM_LIMIT);
    }

    public function setItemLimit($value)
    {
        return $this->setData(self::ITEM_LIMIT, $value);
    }

    public function getEnableAddToWishlist()
    {
        return $this->getData(self::ENABLE_ADD_TO_WISHLIST);
    }

    public function setEnableAddToWishlist($value)
    {
        return $this->setData(self::ENABLE_ADD_TO_WISHLIST, $value);
    }

    public function getRemoveRelatedBlock()
    {
        return $this->getData(self::REMOVE_RELATED_BLOCK);
    }

    public function setRemoveRelatedBlock($value)
    {
        return $this->setData(self::REMOVE_RELATED_BLOCK, $value);
    }

    public function getSeparatorImage()
    {
        return $this->getData(self::SEPARATOR_IMAGE);
    }

    public function setSeparatorImage($value)
    {
        return $this->setData(self::SEPARATOR_IMAGE, $value);
    }

    public function getUsePopup()
    {
        return $this->getData(self::USE_POPUP);
    }

    public function setUsePopup($value)
    {
        return $this->setData(self::USE_POPUP, $value);
    }
}
