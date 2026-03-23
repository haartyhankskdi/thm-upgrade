<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Ui\Component\Form;

/**
 * Class ScheduleMinutesTime
 */
class ScheduleMinutesTime implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get minute options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $minutesArray = [];
        for ($i = 0; $i < 60; $i++) {
            $minutesArray[] = [
                'value' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'label' => str_pad($i, 2, '0', STR_PAD_LEFT)
            ];
        }

        return $minutesArray;
    }
}
