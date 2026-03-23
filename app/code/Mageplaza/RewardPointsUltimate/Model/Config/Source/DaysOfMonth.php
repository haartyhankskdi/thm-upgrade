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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DaysOfMonth
 * @package Mageplaza\RewardPointsUltimate\Model\Config\Source
 */
class DaysOfMonth implements ArrayInterface
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        for ($i = 1; $i < 32; $i++) {
            $options[$i] = $i;
        }

        return $options;
    }
}
