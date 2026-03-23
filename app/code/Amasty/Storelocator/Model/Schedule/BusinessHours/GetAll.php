<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Schedule\BusinessHours;

use Amasty\Storelocator\Api\Data\BusinessHoursInterface;
use Amasty\Storelocator\Model\Schedule;
use Amasty\Storelocator\Model\Schedule\Weekdays;

class GetAll
{
    /**
     * @var GetByWeekday
     */
    private $getByWeekday;

    /**
     * @var Weekdays
     */
    private $weekdays;

    public function __construct(
        GetByWeekday $getByWeekday,
        Weekdays $weekdays
    ) {
        $this->getByWeekday = $getByWeekday;
        $this->weekdays = $weekdays;
    }

    /**
     * @param Schedule $schedule
     * @return BusinessHoursInterface[]
     */
    public function execute(Schedule $schedule): array
    {
        $result = [];
        foreach (array_keys($this->weekdays->toArray()) as $weekday) {
            $result[] = $this->getByWeekday->execute($schedule, $weekday);
        }

        return $result;
    }
}
