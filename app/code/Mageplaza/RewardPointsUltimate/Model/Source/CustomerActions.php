<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CustomerActions
 * @package Mageplaza\RewardPointsUltimate\Model\Source
 */
class CustomerActions implements OptionSourceInterface
{
    const TYPE_FIXED_POINTS = 'fixed_points';
    const TYPE_PRICE = 'price';
    const TYPE_FIXED_DISCOUNT = 'fixed_discount';
    const TYPE_PERCENT = 'percent_discount';

    /**
     * Retrieve option array
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::TYPE_FIXED_POINTS => __('Give fixed X points to Customers'),
            self::TYPE_PRICE => __('Give X points for every Y amount of Price'),
            self::TYPE_FIXED_DISCOUNT => __('Give a fixed discount to Customer'),
            self::TYPE_PERCENT => __('Give percent discount to Customer')
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
