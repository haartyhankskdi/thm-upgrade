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
 * Class ReferralActions
 * @package Mageplaza\RewardPointsUltimate\Model\Source
 */
class ReferralActions implements OptionSourceInterface
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
            self::TYPE_FIXED => __('Give fixed X points to Referer'),
            self::TYPE_PRICE => __('Give X points for every Y amount of Price')
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
