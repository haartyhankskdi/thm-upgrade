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

namespace Mageplaza\RewardPointsUltimate\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DaysOfWeek
 * @package Mageplaza\RewardPointsUltimate\Model\Config\Source
 */
class DaysOfWeek implements ArrayInterface
{
    const SUNDAY    = '0';
    const MONDAY    = '1';
    const TUESDAY   = '2';
    const WEDNESDAY = '3';
    const THURSDAY  = '4';
    const FRIDAY    = '5';
    const SATURDAY  = '6';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::SUNDAY    => __('Sunday'),
            self::MONDAY    => __('Monday'),
            self::TUESDAY   => __('Tuesday'),
            self::WEDNESDAY => __('Wednesday'),
            self::THURSDAY  => __('Thursday'),
            self::FRIDAY    => __('Friday'),
            self::SATURDAY  => __('Saturday'),
        ];
    }
}
