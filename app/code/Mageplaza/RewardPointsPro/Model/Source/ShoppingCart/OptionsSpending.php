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
 * Class OptionsSpending
 * @package Mageplaza\RewardPointsPro\Model\Source\ShoppingCart
 */
class OptionsSpending implements OptionSourceInterface
{
    const TYPE_FIXED = 'fixed';
    const TYPE_PRICE = 'price';

    /**
     * Retrieve option array
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::TYPE_FIXED => __('Give discount for fixed X points'),
            self::TYPE_PRICE => __('Give discount for every X points'),
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
        foreach ($this::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
