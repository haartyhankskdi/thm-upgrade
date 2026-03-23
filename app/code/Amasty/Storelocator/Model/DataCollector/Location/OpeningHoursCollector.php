<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\DataCollector\Location;

use Amasty\Storelocator\Api\Data\BusinessHoursInterface;
use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Api\ScheduleRepositoryInterface;
use Amasty\Storelocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\Schedule\FormatTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class OpeningHoursCollector implements LocationCollectorInterface
{
    /**
     * @var ScheduleRepositoryInterface
     */
    private $scheduleRepository;

    /**
     * @var FormatTime
     */
    private $formatTime;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var array
     */
    private $openingHoursData = [];

    /**
     * @var string
     */
    private $currentDay = '';

    public function __construct(
        ScheduleRepositoryInterface $scheduleRepository,
        FormatTime $formatTime,
        ConfigProvider $configProvider,
        TimezoneInterface $timezone
    ) {
        $this->scheduleRepository = $scheduleRepository;
        $this->formatTime = $formatTime;
        $this->configProvider = $configProvider;
        $this->timezone = $timezone;
    }

    public function initialize(): void
    {
        $this->currentDay = strtolower($this->timezone->date()->format('l'));
        $schedules = $this->scheduleRepository->getList()->getItems();

        foreach ($schedules as $schedule) {
            $openingHours = [];
            foreach ($schedule->getBusinessHours() as $businessHours) {
                $openingHours[$businessHours->getWeekday()] = $businessHours->isOpen() ?
                    $this->getOpeningHours($businessHours) :
                    $this->configProvider->getClosedText();
            }

            $this->openingHoursData[$schedule->getId()] = $openingHours;
        }
    }

    public function collect(LocationInterface $location): void
    {
        $result = '';
        if ($location->getSchedule() && isset($this->openingHoursData[$location->getSchedule()][$this->currentDay])) {
            $result = $this->openingHoursData[$location->getSchedule()][$this->currentDay];
        }
        $location->setWorkingTimeToday($result);
    }

    private function getOpeningHours(BusinessHoursInterface $businessHours): string
    {
        return sprintf(
            '%s - %s',
             date('h:i', strtotime($businessHours->getOpenFrom())) . ' AM',
          date('h:i', strtotime($businessHours->getOpenTo())) . ' PM'

        );
    }

    public function _resetState(): void
    {
        $this->openingHoursData = [];
    }
}
