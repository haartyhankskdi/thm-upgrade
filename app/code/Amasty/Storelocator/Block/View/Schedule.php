<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Block\View;

use Amasty\Storelocator\Api\Data\BusinessHoursInterface;
use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Api\Data\ScheduleInterface;
use Amasty\Storelocator\Api\ScheduleRepositoryInterface;
use Amasty\Storelocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\Schedule\FormatTime;
use Amasty\Storelocator\Model\Schedule\Weekdays;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @todo replace with view model after splitting the location block
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Schedule extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Storelocator::schedule.phtml';

    /**
     * @var ScheduleRepositoryInterface
     */
    private $scheduleRepository;

    /**
     * @var Weekdays
     */
    private $weekdays;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var FormatTime
     */
    private $formatTime;

    public function __construct(
        Context $context,
        ScheduleRepositoryInterface $scheduleRepository,
        Weekdays $weekdays,
        ConfigProvider $configProvider,
        FormatTime $formatTime,
        array $data = []
    ) {
        $this->scheduleRepository = $scheduleRepository;
        $this->weekdays = $weekdays;
        $this->configProvider = $configProvider;
        $this->formatTime = $formatTime;
        parent::__construct($context, $data);
    }

    public function getSchedule(?LocationInterface $location): ?ScheduleInterface
    {
        try {
            //must return copy to avoid both caching and reloading
            return clone $this->scheduleRepository->get((int)$location->getSchedule());
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getDayName(string $weekday): ?string
    {
        $dayNames = $this->weekdays->toArray();

        return isset($dayNames[$weekday]) ? (string) $dayNames[$weekday] : null;
    }

    public function getClosedText(): string
    {
        return $this->configProvider->getClosedText();
    }

    public function getBreakText(): string
    {
        return $this->configProvider->getBreakText();
    }

    public function formatOpeningHours(BusinessHoursInterface $businessHours): string
    {
        return $this->formatTimePeriod($businessHours->getOpenFrom(), $businessHours->getOpenTo());
    }

    public function formatBreakPeriod(BusinessHoursInterface $businessHours): string
    {
        return $this->formatTimePeriod($businessHours->getBreakFrom(), $businessHours->getBreakTo());
    }

    /**
     * @param string $from
     * @param string $to
     * @return string
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private function formatTimePeriod(string $from, string $to): string
    {
        return sprintf(
            '%s - %s',
            date('h:i', strtotime($this->formatTime->execute($from))) . ' AM',
            date('h:i', strtotime($this->formatTime->execute($to))) . ' PM'

        );
    }
}
