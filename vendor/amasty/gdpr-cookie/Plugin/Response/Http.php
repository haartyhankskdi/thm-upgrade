<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Plugin\Response;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Model\CookieManager;
use Amasty\GdprCookie\Model\CookiePolicy;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Store\Model\StoreManagerInterface;
use Magento\MediaStorage\Model\File\Storage\Response as FileStorageResponse;

class Http
{
    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var CookiePolicy
     */
    private $cookiePolicy;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    public function __construct(
        CookiePolicy $cookiePolicy,
        CookieManager $cookieManager,
        StoreManagerInterface $storeManager,
        CookieManagementInterface $cookieManagement
    ) {
        $this->cookiePolicy = $cookiePolicy;
        $this->cookieManager = $cookieManager;
        $this->storeManager = $storeManager;
        $this->cookieManagement = $cookieManagement;
    }

    public function beforeSendResponse(HttpResponse $subject)
    {
        if ($subject instanceof FileStorageResponse) {
            return;
        }

        $allowedGroups = $this->cookieManager->getAllowCookies();

        if ($this->cookiePolicy->isCookiePolicyAllowed() && $allowedGroups) {
            $storeId = (int)$this->storeManager->getStore()->getId();
            $rejectedCookieNames = [];

            if (!in_array($allowedGroups, [CookieManager::ALLOWED_ALL, CookieManager::ALLOWED_NONE])) {
                $allowedGroupIds = array_map('trim', explode(',', $allowedGroups));
                $rejectedCookieNames = array_map(function ($cookie) {
                    return $cookie->getName();
                }, $this->cookieManagement->getNotAssignedCookiesToGroups($storeId, $allowedGroupIds));
            } elseif ($allowedGroups === CookieManager::ALLOWED_NONE) {
                $rejectedCookieNames = array_map(function ($cookie) {
                    return $cookie->getName();
                }, $this->cookieManagement->getCookies($storeId));
            }

            if ($rejectedCookieNames) {
                $this->cookieManager->deleteCookies($rejectedCookieNames);
            }
        }
    }
}
