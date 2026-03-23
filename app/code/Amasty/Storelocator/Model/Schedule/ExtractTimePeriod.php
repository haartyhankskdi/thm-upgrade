<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Schedule;

use Amasty\Storelocator\Model\Schedule;

class ExtractTimePeriod
{
    public const FROM = 'from';
    public const TO = 'to';

    /**
     * @param array{hours: string, minutes: string} $from
     * @param array{hours: string, minutes: string} $to
     * @return array{from: string, to: string}|null
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function execute(array $from, array $to): ?array
    {
        if (!$this->validate($from, $to)) {
            return null;
        }

        return [
            self::FROM => $this->formatTime($from),
            self::TO => $this->formatTime($to)
        ];
    }

    /**
     * @param array $from
     * @param array $to
     * @return bool
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private function validate(array $from, array $to): bool
    {
        return !empty($from[Schedule::TIME_HOURS])
            && !empty($from[Schedule::TIME_MINUTES])
            && !empty($to[Schedule::TIME_HOURS])
            && !empty($to[Schedule::TIME_MINUTES]);
    }

    private function formatTime(array $timeData): string
    {
        return sprintf(
            '%s:%s',
            $timeData[Schedule::TIME_HOURS],
            $timeData[Schedule::TIME_MINUTES]
        );
    }
}
