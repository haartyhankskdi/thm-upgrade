<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Schedule;

class Weekdays
{
    public const MONDAY = 'monday';
    public const TUESDAY = 'tuesday';
    public const WEDNESDAY = 'wednesday';
    public const THURSDAY = 'thursday';
    public const FRIDAY = 'friday';
    public const SATURDAY = 'saturday';
    public const SUNDAY = 'sunday';

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            self::MONDAY    => __('Monday'),
            self::TUESDAY   => __('Tuesday'),
            self::WEDNESDAY => __('Wednesday'),
            self::THURSDAY  => __('Thursday'),
            self::FRIDAY    => __('Friday'),
            self::SATURDAY  => __('Saturday'),
            self::SUNDAY    => __('Sunday')
        ];
    }
}
