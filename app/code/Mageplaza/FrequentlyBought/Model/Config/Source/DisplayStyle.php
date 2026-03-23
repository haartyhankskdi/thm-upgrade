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
 * @package     Mageplaza_FrequentlyBought
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FrequentlyBought\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class DisplayStyle
 * @package Mageplaza\FrequentlyBought\Model\Config\Source
 */
class DisplayStyle implements OptionSourceInterface
{
    const DEFAULT = 'default';
    const SLIDER = 'slider';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DEFAULT, 'label' => __('Default')],
            ['value' => self::SLIDER, 'label' => __('Slider')]
        ];
    }
}
