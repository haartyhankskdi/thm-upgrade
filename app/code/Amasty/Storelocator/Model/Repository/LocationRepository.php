<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Repository;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\ResourceModel\Location as LocationResource;
use Amasty\Storelocator\Model\LocationFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class LocationRepository
{
    /**
     * @var LocationInterface[]
     */
    private $locations;

    /**
     * @var LocationResource
     */
    private $locationResource;

    /**
     * @var LocationFactory
     */
    private $locationFactory;

    public function __construct(
        LocationResource $locationResource,
        LocationFactory $locationFactory
    ) {
        $this->locationResource = $locationResource;
        $this->locationFactory = $locationFactory;
    }

    /**
     * @param int $id
     * @return LocationInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): LocationInterface
    {
        if (!isset($this->locations[$id])) {
            $location = $this->locationFactory->create();
            $this->locationResource->load($location, $id);

            if (!$location->getId()) {
                throw new NoSuchEntityException(__('Location with specified ID "%1" not found.', $id));
            }

            $this->locations[$id] = $location;
        }

        return $this->locations[$id];
    }

    public function _resetState(): void
    {
        $this->locations = null;
    }
}
