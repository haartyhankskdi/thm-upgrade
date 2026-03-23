<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Schedule;

use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Model\Schedule;

class UnserializeSchedule
{
    /**
     * @var Serializer
     */
    private $jsonSerializer;

    public function __construct(Serializer $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute(Schedule $schedule): ?array
    {
        $serializedSchedule = $schedule->getSchedule();
        if (empty($serializedSchedule)) {
            return null;
        }

        return $this->jsonSerializer->unserialize($serializedSchedule) ?: null;
    }
}
