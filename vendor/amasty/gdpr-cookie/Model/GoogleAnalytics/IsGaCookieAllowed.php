<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\GoogleAnalytics;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Model\CookieManager;
use Amasty\GdprCookie\Model\CookiePolicy;
use Magento\Store\Model\StoreManagerInterface;

class IsGaCookieAllowed
{
    public const COOKIE_GA = '_ga';

    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CookiePolicy
     */
    private $cookiePolicy;

    public function __construct(
        CookieManager $cookieManager,
        CookieManagementInterface $cookieManagement,
        StoreManagerInterface $storeManager,
        CookiePolicy $cookiePolicy
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieManagement = $cookieManagement;
        $this->storeManager = $storeManager;
        $this->cookiePolicy = $cookiePolicy;
    }

    public function execute(): bool
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $allowedGroups = $this->cookieManager->getAllowCookies();
        $isGaEssential = false;
        $isGaAllowed = true;

        foreach ($this->cookieManagement->getEssentialCookies($storeId) as $essentialCookie) {
            if ($essentialCookie->getName() === self::COOKIE_GA) {
                $isGaEssential = true;
            }
        }

        if ($allowedGroups === CookieManager::ALLOWED_ALL || !$this->cookiePolicy->isCookiePolicyAllowed()) {
            return true;
        }

        if ((!$allowedGroups || $allowedGroups === CookieManager::ALLOWED_NONE) && !$isGaEssential) {
            $isGaAllowed = false;
        }

        if ($allowedGroups) {
            $allowedGroupIds = array_map('trim', explode(',', $allowedGroups));
            $rejectedCookies = $this->cookieManagement->getNotAssignedCookiesToGroups($storeId, $allowedGroupIds);

            foreach ($rejectedCookies as $cookie) {
                if ($cookie->getName() === self::COOKIE_GA) {
                    $isGaAllowed = false;
                    break;
                }
            }
        }

        return $isGaAllowed;
    }
}
