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
 * Class Frequency
 * @package Mageplaza\RewardPointsUltimate\Model\Config\Source
 */
class Frequency implements ArrayInterface
{
    const WEEKLY  = 'W';
    const MONTHLY = 'M';
    const YEARLY  = 'Y';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Weekly'), 'value' => self::WEEKLY],
            ['label' => __('Monthly'), 'value' => self::MONTHLY],
            ['label' => __('Yearly'), 'value' => self::YEARLY],
        ];
    }
}
