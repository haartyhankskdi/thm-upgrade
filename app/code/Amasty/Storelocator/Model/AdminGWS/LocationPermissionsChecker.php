<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\AdminGWS;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\Location;
use Magento\AdminGws\Model\Role;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class LocationPermissionsChecker
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(StoreRepositoryInterface $storeRepository, ObjectManagerInterface $objectManager)
    {
        $this->storeRepository = $storeRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * @param Location $model
     * @throws LocalizedException
     */
    public function locationSaveBefore(Location $model): void
    {
        $role = $this->getRole();

        if (!$role) {
            return;
        }

        // Deny creating new rule entity if role has no allowed website ids
        if (!$model->getId() && !$role->getIsWebsiteLevel()) {
            throw new LocalizedException(__('More permissions are needed to save this item.'));
        }

        $storeIds = $model->isObjectNew()
            ? (string)$model->getData(LocationInterface::STORES)
            : (string)$model->getOrigData(LocationInterface::STORES);

        $websiteIds = $this->getWebsiteIds($storeIds);

        // Deny saving rule entity if role has no exclusive access to assigned to location entity websites
        // Check if original websites list is empty implemented to deny saving target rules for all GWS limited users
        if ($model->getId() && (!$role->hasExclusiveAccess($websiteIds))) {
            throw new LocalizedException(__('More permissions are needed to save this location.'));
        }
    }

    /**
     * Validate location before delete
     *
     * @param Location $model
     * @return void
     * @throws LocalizedException
     */
    public function locationDeleteBefore(Location $model): void
    {
        $role = $this->getRole();

        if (!$role) {
            return;
        }

        $storeIds = (string)$model->getOrigData(LocationInterface::STORES);
        $websiteIds = $this->getWebsiteIds($storeIds);

        // Deny deleting rule entity if role has no exclusive access to assigned to location entity websites
        // Check if original websites list is empty implemented to deny deleting target rules for all GWS limited users
        if (!$role->hasExclusiveAccess($websiteIds)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('More permissions are needed to delete this location.')
            );
        }
    }

    /**
     * @param string $storeIdsString
     * @return int[]
     */
    private function getWebsiteIds(string $storeIdsString): array
    {
        $stores = $this->storeRepository->getList();
        $locationStoreIds = explode(',', $storeIdsString);
        $locationStoreIds = array_filter($locationStoreIds);

        $websiteIds = [];
        foreach ($stores as $store) {
            if (in_array($store->getId(), $locationStoreIds)) {
                $websiteIds[] = (int)$store->getWebsiteId();
            }
        }

        return $websiteIds;
    }

    /**
     * @return Role|null
     * @phpstan-ignore-next-line
     */
    private function getRole(): ?Role
    {
        if (class_exists(Role::class)) {
            return $this->objectManager->get(Role::class);
        }

        return null;
    }
}
