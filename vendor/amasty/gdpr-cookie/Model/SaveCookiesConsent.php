<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Api\CookieManagementInterface;

class SaveCookiesConsent
{
    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var CookieConsentLogger
     */
    private $consentLogger;

    public function __construct(
        CookieManagementInterface $cookieManagement,
        CookieManager $cookieManager,
        CookieConsentLogger $consentLogger
    ) {
        $this->cookieManagement = $cookieManagement;
        $this->cookieManager = $cookieManager;
        $this->consentLogger = $consentLogger;
    }

    public function execute(array $allowedCookieGroupIds, int $storeId = 0, int $customerId = 0): array
    {
        if (!$allowedCookieGroupIds) {
            $rejectedCookieNames = array_map(function ($cookie) {
                return $cookie->getName();
            }, $this->cookieManagement->getCookies($storeId));
            $this->cookieManager->deleteCookies($rejectedCookieNames);
            $this->cookieManager->updateAllowedCookies(CookieManager::ALLOWED_NONE);
            $this->consentLogger->logCookieConsent($customerId);
        } else {
            $this->consentLogger->logCookieConsent($customerId, $allowedCookieGroupIds);
            $rejectedCookieNames = array_map(function ($cookie) {
                return $cookie->getName();
            }, $this->cookieManagement->getNotAssignedCookiesToGroups($storeId, $allowedCookieGroupIds));
            $this->cookieManager->deleteCookies($rejectedCookieNames);
            $this->cookieManager->updateAllowedCookies(implode(',', $allowedCookieGroupIds));
        }

        $result['success'] = true;
        $result['message'] = 'You saved your cookie settings!';

        return $result;
    }
}
