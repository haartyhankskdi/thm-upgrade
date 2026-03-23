<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Ui\Component\Form;

class ScheduleStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    public const OPEN_STATUS = 1;
    public const CLOSE_STATUS = 0;

    /**
     * Getting schedule status options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::OPEN_STATUS, 'label' => __('Open')],
            ['value' => self::CLOSE_STATUS, 'label' => __('Closed')]
        ];
    }
}
