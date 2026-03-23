<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Api;

use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Amasty\GdprCookie\Api\Data\CookieInterface;

interface CookieManagementInterface
{
    /**
     * @param int $storeId
     * @param int $groupId
     * @return CookieInterface[]
     */
    public function getCookies(int $storeId = 0, int $groupId = 0): array;

    /**
     * @param int $storeId
     * @return CookieInterface[]
     */
    public function getEssentialCookies(int $storeId = 0): array;

    /**
     * @param int $storeId
     * @param array $groupIds
     * @return CookieInterface[]
     */
    public function getNotAssignedCookiesToGroups(int $storeId = 0, array $groupIds = []): array;

    /**
     * @param int $storeId
     * @param array $groupIds
     * @return CookieGroupsInterface[]
     */
    public function getGroups(int $storeId = 0, array $groupIds = []): array;

    /**
     * @param int $websiteId
     * @return CookieGroupsInterface[]
     */
    public function getAvailableGroups(int $websiteId): array;
}
