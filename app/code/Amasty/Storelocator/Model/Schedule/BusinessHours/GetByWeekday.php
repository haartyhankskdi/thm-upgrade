<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Schedule\BusinessHours;

use Amasty\Storelocator\Api\Data\BusinessHoursInterface;
use Amasty\Storelocator\Api\Data\BusinessHoursInterfaceFactory;
use Amasty\Storelocator\Model\Schedule;
use Amasty\Storelocator\Model\Schedule\ExtractTimePeriod;
use Amasty\Storelocator\Model\Schedule\UnserializeSchedule;
use Amasty\Storelocator\Model\Schedule\WorkingTimeFactory;

class GetByWeekday
{
    /**
     * @var UnserializeSchedule
     */
    private $unserializeSchedule;

    /**
     * @var ExtractTimePeriod
     */
    private $extractTimePeriod;

    /**
     * @var BusinessHoursInterfaceFactory
     */
    private $businessHoursFactory;

    public function __construct(
        UnserializeSchedule $unserializeSchedule,
        ExtractTimePeriod $extractTimePeriod,
        BusinessHoursInterfaceFactory $workingTimeFactory
    ) {
        $this->unserializeSchedule = $unserializeSchedule;
        $this->extractTimePeriod = $extractTimePeriod;
        $this->businessHoursFactory = $workingTimeFactory;
    }

    public function execute(Schedule $schedule, string $weekday): BusinessHoursInterface
    {
        $businessHours = $this->businessHoursFactory->create();
        $businessHours->setWeekday($weekday);

        $daySchedule = $this->getScheduleForDay($schedule, $weekday);
        $dayStatus = $daySchedule[$weekday . '_status'] ?? false;
        if (!$dayStatus) {
            $businessHours->setIsOpen(false);
            return $businessHours;
        }

        $openingHours = $this->getOpeningHours($daySchedule);

        if ($openingHours === null) {
            $businessHours->setIsOpen(false);
            return $businessHours;
        }

        $businessHours->setIsOpen(true);
        $businessHours->setOpenFrom($openingHours[ExtractTimePeriod::FROM]);
        $businessHours->setOpenTo($openingHours[ExtractTimePeriod::TO]);

        $breakPeriod = $this->getBreakPeriod($daySchedule);
        if ($breakPeriod !== null) {
            $businessHours->setBreakFrom($breakPeriod[ExtractTimePeriod::FROM]);
            $businessHours->setBreakTo($breakPeriod[ExtractTimePeriod::TO]);
        }

        return $businessHours;
    }

    private function getScheduleForDay(Schedule $schedule, string $weekday): ?array
    {
        $unserializedSchedule = $this->unserializeSchedule->execute($schedule);
        if ($unserializedSchedule === null) {
            return null;
        }

        return $unserializedSchedule[$weekday] ?? null;
    }

    private function getOpeningHours(array $daySchedule): ?array
    {
        if (empty($daySchedule[Schedule::OPEN_TIME]) || empty($daySchedule[Schedule::CLOSE_TIME])) {
            return null;
        }

        return $this->extractTimePeriod->execute(
            $daySchedule[Schedule::OPEN_TIME],
            $daySchedule[Schedule::CLOSE_TIME]
        );
    }

    private function getBreakPeriod(array $daySchedule): ?array
    {
        if (empty($daySchedule[Schedule::BREAK_FROM]) || empty($daySchedule[Schedule::CLOSE_TIME])) {
            return null;
        }

        $breakPeriodFrom = $daySchedule[Schedule::BREAK_FROM];
        $breakPeriodTo = $daySchedule[Schedule::BREAK_TO];

        $breakPeriod = $this->extractTimePeriod->execute($breakPeriodFrom, $breakPeriodTo);
        if ($breakPeriod === null) {
            return null;
        }

        return $this->isBreakPeriodValid($breakPeriodFrom, $breakPeriodTo) ? $breakPeriod : null;
    }

    /**
     * @param array $from
     * @param array $to
     * @return bool
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private function isBreakPeriodValid(array $from, array $to): bool
    {
        $areHoursEqual = $from[Schedule::TIME_HOURS] === $to[Schedule::TIME_HOURS];
        if (!$areHoursEqual) {
            return true;
        }

        return $from[Schedule::TIME_MINUTES] !== $to[Schedule::TIME_MINUTES];
    }
}
