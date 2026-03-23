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
 * Interface RuleLabelInterface
 * @package Mageplaza\RewardPointsPro\Api\Data
 */
interface RuleLabelInterface
{
    const KEY_STORE_ID = 'store_id';
    const KEY_LABEL    = 'label';

    /**
     * Get storeId
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Return the label for the store
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set the label for the store
     *
     * @param string $storeLabel
     * @return $this
     */
    public function setLabel($storeLabel);
}
