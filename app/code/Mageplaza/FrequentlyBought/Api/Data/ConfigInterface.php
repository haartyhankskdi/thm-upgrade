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

namespace Mageplaza\FrequentlyBought\Api\Data;

/**
 * Interface ConfigInterface
 * @package Mageplaza\FrequentlyBought\Api\Data
 */
interface ConfigInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get Is Enable
     *
     * @return bool
     */
    public function getIsEnable();

    /**
     * Set SKU
     *
     * @param int $bool
     * @return $this
     */
    public function setIsEnable($bool);

    /**
     * Get Product Method
     *
     * @return string
     */
    public function getProductMethod();

    /**
     * Set Product Method
     *
     * @param string $value
     * @return $this
     */
    public function setProductMethod($value);

    /**
     * Get Block Name
     *
     * @return string
     */
    public function getBlockName();

    /**
     * Set Block Name
     *
     * @param string $value
     * @return $this
     */
    public function setBlockName($value);

    /**
     * Get Item Limit
     *
     * @return int
     */
    public function getItemLimit();

    /**
     * Set Item Limit
     *
     * @param int $value
     * @return $this
     */
    public function setItemLimit($value);

    /**
     * Get IS Enable Add To Wishlist
     *
     * @return bool
     */
    public function getEnableAddToWishlist();

    /**
     * Set IS Enable Add To Wishlist
     *
     * @param int $value
     * @return $this
     */
    public function setEnableAddToWishlist($value);

    /**
     * Get Remove Related Block
     *
     * @return bool
     */
    public function getRemoveRelatedBlock();

    /**
     * Set Remove Related Block
     *
     * @param int $value
     * @return $this
     */
    public function setRemoveRelatedBlock($value);

    /**
     * Get Separator Image
     *
     * @return string
     */
    public function getSeparatorImage();

    /**
     * Set Separator Image
     *
     * @param string $value
     * @return $this
     */
    public function setSeparatorImage($value);

    /**
     * Get Use Popup
     *
     * @return bool
     */
    public function getUsePopup();

    /**
     * Set Use Popup
     *
     * @param int $value
     * @return $this
     */
    public function setUsePopup($value);
}
