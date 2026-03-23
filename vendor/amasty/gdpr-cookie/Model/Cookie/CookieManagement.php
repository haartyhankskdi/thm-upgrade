<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Cookie;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Amasty\GdprCookie\Api\Data\CookieInterface;
use Amasty\GdprCookie\Model\ResourceModel\Cookie;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @api
 */
class CookieManagement implements CookieManagementInterface
{
    /**
     * @var Cookie\CollectionFactory
     */
    protected $cookieCollectionFactory;

    /**
     * @var CookieGroup\CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Cookie\CollectionFactory $cookieCollectionFactory,
        CookieGroup\CollectionFactory $groupCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->cookieCollectionFactory = $cookieCollectionFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->storeManager = $storeManager;
    }

    public function getCookies(int $storeId = 0, int $groupId = 0): array
    {
        $collection = $this->createCookieCollection($storeId);

        if ($groupId) {
            $collection->addFieldToFilter(CookieInterface::GROUP_ID, ['eq' => $groupId]);
        }

        return $collection->getItems();
    }

    public function getEssentialCookies(int $storeId = 0): array
    {
        $collection = $this->createCookieCollection($storeId)
            ->joinGroup()
            ->addFieldToFilter('groups.' . CookieGroupsInterface::IS_ESSENTIAL, ['eq' => 1]);

        return $collection->getItems();
    }

    public function getNotAssignedCookiesToGroups(int $storeId = 0, array $groupIds = []): array
    {
        $collection = $this->createCookieCollection($storeId);

        if ($groupIds) {
            $collection->addFieldToFilter(CookieInterface::GROUP_ID, ['nin' => $groupIds]);
        }

        return $collection->getItems();
    }

    public function getGroups(int $storeId = 0, array $groupIds = []): array
    {
        $collection = $this->groupCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addFieldToFilter(CookieGroupsInterface::IS_ENABLED, ['eq' => 1]);
        $collection->setOrder(CookieGroupsInterface::SORT_ORDER, $collection::SORT_ORDER_ASC);
        $collection->setOrder(CookieGroupsInterface::ID, $collection::SORT_ORDER_ASC);

        if ($groupIds) {
            $collection->addFieldToFilter(CookieGroupsInterface::ID, ['in' => $groupIds]);
        }

        return $collection->getItems();
    }

    public function getAvailableGroups(int $websiteId): array
    {
        $groups = [];
        /** @var StoreInterface[] $stores */
        $stores = $this->storeManager->getWebsite($websiteId)->getStores();
        foreach ($stores as $store) {
            if ($store->getIsActive()) {
                $groups += $this->getGroups((int)$store->getId());
            }
        }
        ksort($groups);

        return $groups;
    }

    protected function createCookieCollection(int $storeId = 0)
    {
        $collection = $this->cookieCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addFieldToFilter(CookieInterface::IS_ENABLED, ['eq' => 1]);

        return $collection;
    }
}
