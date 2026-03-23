<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\DataCollector\Location;

use Amasty\Storelocator\Api\Data\LocationInterface;
use InvalidArgumentException;

class CompositeCollector
{
    /**
     * @var LocationCollectorInterface[]
     */
    private $dataCollectors;

    public function __construct(
        array $dataCollectors = []
    ) {
        foreach ($dataCollectors as $dataCollector) {
            if (!($dataCollector instanceof LocationCollectorInterface)) {
                throw new InvalidArgumentException(
                    'Type "' . get_class($dataCollector) . '" is not instance of ' . LocationCollectorInterface::class
                );
            }
            $dataCollector->initialize();
        }
        $this->dataCollectors = $dataCollectors;
    }

    public function collect(LocationInterface $location): void
    {
        foreach ($this->dataCollectors as $collector) {
            $collector->collect($location);
        }
    }
}
