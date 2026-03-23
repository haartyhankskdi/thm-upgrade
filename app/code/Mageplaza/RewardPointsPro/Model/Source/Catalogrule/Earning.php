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

namespace Mageplaza\RewardPointsPro\Model\Source\Catalogrule;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Earning
 * @package Mageplaza\RewardPointsPro\Model\Source\Catalogrule
 */
class Earning implements OptionSourceInterface
{
    const TYPE_FIXED   = 'fixed';
    const TYPE_PRICE   = 'price';
    const TYPE_PROFIT  = 'profit';
    const TYPE_PERCENT = 'percent';

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::TYPE_FIXED   => __('Give fixed X points to Customers'),
            self::TYPE_PRICE   => __('Give X points for every Y amount of Price'),
            self::TYPE_PROFIT  => __('Give X points for every Y amount of Profit'),
            self::TYPE_PERCENT => __('Give points according to X% of original price')
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
