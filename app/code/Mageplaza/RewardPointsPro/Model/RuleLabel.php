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

use Magento\Framework\Api\AbstractSimpleObject;
use Mageplaza\RewardPointsPro\Api\Data\RuleLabelInterface;

/**
 * Class RuleLabel
 * @package Mageplaza\RewardPointsPro\Model
 */
class RuleLabel extends AbstractSimpleObject implements RuleLabelInterface
{
    /**
     * Get storeId
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_get(self::KEY_STORE_ID);
    }

    /**
     * Set store id
     *
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::KEY_STORE_ID, $storeId);
    }

    /**
     * Return the label for the store
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_get(self::KEY_LABEL);
    }

    /**
     * Set the label for the store
     *
     * @param string $storeLabel
     *
     * @return $this
     */
    public function setLabel($storeLabel)
    {
        return $this->setData(self::KEY_LABEL, $storeLabel);
    }
}
