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
 * Class MonthsOfYear
 * @package Mageplaza\RewardPointsUltimate\Model\Config\Source
 */
class MonthsOfYear implements ArrayInterface
{
    const JANUARY   = 1;
    const FEBRUARY  = 2;
    const MARCH     = 3;
    const APRIL     = 4;
    const MAY       = 5;
    const JUNE      = 6;
    const JULY      = 7;
    const AUGUST    = 8;
    const SEPTEMBER = 9;
    const OCTOBER   = 10;
    const NOVEMBER  = 11;
    const DECEMBER  = 12;

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::JANUARY   => __('January'),
            self::FEBRUARY  => __('February'),
            self::MARCH     => __('March'),
            self::APRIL     => __('April'),
            self::MAY       => __('May'),
            self::JUNE      => __('June'),
            self::JULY      => __('July'),
            self::AUGUST    => __('August'),
            self::SEPTEMBER => __('September'),
            self::OCTOBER   => __('October'),
            self::NOVEMBER  => __('November'),
            self::DECEMBER  => __('December'),
        ];
    }
}
