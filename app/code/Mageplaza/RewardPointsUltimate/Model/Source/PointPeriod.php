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

/**
 * Class PointPeriod
 * @package Mageplaza\RewardPointsUltimate\Model\Source
 */
class PointPeriod extends AbstractSource
{
    const PER_DAY = 'day';
    const PER_MONTH = 'month';
    const PER_YEAR = 'year';
    const LIFETIME = 'lifetime';

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::PER_DAY => __('Per day'),
            self::PER_MONTH => __('Per month'),
            self::PER_YEAR => __('Per year'),
            self::LIFETIME => __('Lifetime')
        ];
    }
}
