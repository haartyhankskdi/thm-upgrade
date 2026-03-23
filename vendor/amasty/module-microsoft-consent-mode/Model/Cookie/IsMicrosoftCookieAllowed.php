<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Model\Cookie;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Model\CookieManager;
use Amasty\GdprCookie\Model\CookiePolicy;
use Magento\Store\Model\StoreManagerInterface;

class IsMicrosoftCookieAllowed
{
    public const COOKIE_MICROSOFT = ['MUID', '_uetsid', '_uetvid'];

    public function __construct(
        private readonly CookieManager $cookieManager,
        private readonly CookieManagementInterface $cookieManagement,
        private readonly StoreManagerInterface $storeManager,
        private readonly CookiePolicy $cookiePolicy
    ) {
    }

    public function execute(): bool
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $allowedGroups = $this->cookieManager->getAllowCookies();
        $isMicrosoftEssential = $this->isMicrosoftEssential($storeId);
        $isMicrosoftAllowed = true;

        if ($allowedGroups === CookieManager::ALLOWED_ALL || !$this->cookiePolicy->isCookiePolicyAllowed()) {
            return true;
        }

        if ((!$allowedGroups || $allowedGroups === CookieManager::ALLOWED_NONE) && !$isMicrosoftEssential) {
            $isMicrosoftAllowed = false;
        }

        if ($allowedGroups) {
            $allowedGroupIds = array_map('trim', explode(',', $allowedGroups));
            $rejectedCookies = $this->cookieManagement->getNotAssignedCookiesToGroups($storeId, $allowedGroupIds);

            foreach ($rejectedCookies as $cookie) {
                if (in_array($cookie->getName(), self::COOKIE_MICROSOFT)) {
                    $isMicrosoftAllowed = false;
                    break;
                }
            }
        }

        return $isMicrosoftAllowed;
    }

    public function isMicrosoftEssential(int $storeId): bool
    {
        foreach ($this->cookieManagement->getEssentialCookies($storeId) as $essentialCookie) {
            if (in_array($essentialCookie->getName(), self::COOKIE_MICROSOFT)) {
                return true;
            }
        }

        return false;
    }
}
