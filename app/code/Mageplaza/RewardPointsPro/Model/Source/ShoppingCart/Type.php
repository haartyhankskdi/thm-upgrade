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

namespace Mageplaza\RewardPointsPro\Model\Source\ShoppingCart;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Type
 * @package Mageplaza\RewardPointsPro\Model\Source\ShoppingCart
 */
class Type implements OptionSourceInterface
{
    const SHOPPING_CART_EARNING = 1;
    const SHOPPING_CART_SPENDING = 2;

    /**
     * Retrieve option array
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            self::SHOPPING_CART_EARNING => __('Shopping cart earning'),
            self::SHOPPING_CART_SPENDING => __('Shopping cart spending')
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
