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
 * Interface ProductLinkInterface
 * @package Mageplaza\FrequentlyBought\Api\Data
 */
interface ProductLinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get SKU
     *
     * @return string
     */
    public function getSku();

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get linked product sku
     *
     * @return string
     */
    public function getLinkedProductSku();

    /**
     * Set linked product sku
     *
     * @param string $linkedProductSku
     * @return $this
     */
    public function setLinkedProductSku($linkedProductSku);

    /**
     * Get linked product type (simple, virtual, etc)
     *
     * @return string
     */
    public function getLinkedProductType();

    /**
     * Set linked product type (simple, virtual, etc)
     *
     * @param string $linkedProductType
     * @return $this
     */
    public function setLinkedProductType($linkedProductType);

    /**
     * Get linked item position
     *
     * @return int
     */
    public function getPosition();

    /**
     * Set linked item position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);
}
