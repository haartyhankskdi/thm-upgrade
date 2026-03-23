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
 * Class Method
 * @package Mageplaza\FrequentlyBought\Model\Config\Source
 */
class Method implements OptionSourceInterface
{
    const RELATED = 'related';
    const FBT = 'fbt';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::RELATED, 'label' => __('Related Product')],
            ['value' => self::FBT, 'label' => __('Frequently Bought Together Product')]
        ];
    }
}
