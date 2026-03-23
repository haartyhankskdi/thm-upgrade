<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\CookieConsent;

use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Amasty\GdprCookie\Model\CookieConsent as CookieConsentModel;
use Amasty\GdprCookie\Model\CookieManager;

class CookieGroupProcessor
{
    public const CONSENT_STATUS_REJECTED = 0;
    public const CONSENT_STATUS_ACCEPTED = 1;

    public function getConsentStatus(?array $allowedGroupIds, CookieGroupsInterface $group): int
    {
        if (($allowedGroupIds === null && !$group->isEssential())
            || (!empty($allowedGroupIds) && !in_array($group->getId(), $allowedGroupIds))
        ) {
            return self::CONSENT_STATUS_REJECTED;
        }

        return self::CONSENT_STATUS_ACCEPTED;
    }

    public function getGroupsStatus(?array $allowedGroupIds): int
    {
        if ($allowedGroupIds === null) {
            return CookieConsentModel::GROUPS_STATUS_NONE_ALLOWED;
        } elseif (empty($allowedGroupIds)) {
            return CookieConsentModel::GROUPS_STATUS_ALL_ALLOWED;
        } else {
            return CookieConsentModel::GROUPS_STATUS_SPECIFIC_GROUP;
        }
    }

    public function getAllowedGroupIds(string $allowedGroups): ?array
    {
        if (!in_array($allowedGroups, [CookieManager::ALLOWED_ALL, CookieManager::ALLOWED_NONE, ''])) {
            return explode(',', $allowedGroups);
        } elseif (in_array($allowedGroups, [CookieManager::ALLOWED_NONE, ''])) {
            return null;
        } else {
            return [];
        }
    }
}
