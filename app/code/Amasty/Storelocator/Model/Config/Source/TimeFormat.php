<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Config\Source;

class TimeFormat implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '0',
                'label' => __('24h'),
            ],
            [
                'value' => '1',
                'label' => __('12h'),
            ]
        ];
    }
}
