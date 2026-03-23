<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class CookieManager
{
    public const ALLOW_COOKIES = 'amcookie_allowed';
    public const DISALLOWED_COOKIE_NAMES = 'amcookie_disallowed';
    public const ALLOWED_NONE = '-1';
    public const ALLOWED_ALL = '0';

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    /**
     * Storage for essential cookie names. Must not delete them even if no decision was taken
     * @var array
     */
    private $essentialCookieNames;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string[]
     */
    private $existCookies = [];

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        StoreManagerInterface $storeManager,
        CookieManagementInterface $cookieManagement,
        ?RequestInterface $request = null
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->storeManager = $storeManager;
        $this->cookieManagement = $cookieManagement;
        // OM for backward compatibility
        $this->request = $request ?? ObjectManager::getInstance()->create(RequestInterface::class);
    }

    public function getAllowCookies(): string
    {
        return $this->cookieManager->getCookie(self::ALLOW_COOKIES) ?? '';
    }

    public function updateAllowedCookies(string $allowedCookiesString)
    {
        $allowedCookiesIds = array_map('trim', explode(',', $allowedCookiesString));
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain())
            ->setDurationOneYear();

        try {
            $this->cookieManager->setPublicCookie(self::ALLOW_COOKIES, $allowedCookiesString, $cookieMetadata);

            $rejectedCookieNames = [];
            if ($allowedCookiesString !== self::ALLOWED_ALL) {
                $storeId = (int)$this->storeManager->getStore()->getId();
                $rejectedCookies = $this->cookieManagement->getNotAssignedCookiesToGroups($storeId, $allowedCookiesIds);

                foreach ($rejectedCookies as $cookie) {
                    $rejectedCookieNames[] = $cookie->getName();
                }
            }

            $this->cookieManager->setPublicCookie(
                self::DISALLOWED_COOKIE_NAMES,
                implode(',', $rejectedCookieNames),
                $cookieMetadata
            );
        } catch (\Exception $e) {
            null;
        }
    }

    public function deleteCookies(array $cookieNames)
    {
        try {
            foreach ($cookieNames as $cookieName) {
                if (in_array($cookieName, $this->getEssentialCookieNames() ?? [])) {
                    continue;
                }

                // Process wildcard in cookie name.
                if (preg_match('#\{\s*([^\{\}]*)\s*\}#', $cookieName, $wildcardMatches)) {
                    $pattern = str_replace('*', '.+', $wildcardMatches[1]);
                    foreach ($this->getExistCookies() as $cookie) {
                        if (preg_match('/^' . $pattern . '$/i', $cookie, $matches)) {
                            $this->deleteCookie($matches[0]);
                        }
                    }
                // Process common cookie name.
                } elseif ($this->cookieManager->getCookie($cookieName)) {
                    $this->deleteCookie($cookieName);
                }
            }
        } catch (\Exception $e) {
            null;
        }
    }

    public function _resetState(): void
    {
        $this->essentialCookieNames = null;
        $this->existCookies = null;
    }

    private function getEssentialCookieNames()
    {
        if ($this->essentialCookieNames === null) {
            $storeId = (int)$this->storeManager->getStore()->getId();

            foreach ($this->cookieManagement->getEssentialCookies($storeId) as $cookie) {
                $this->essentialCookieNames[] = $cookie->getName();
            }
        }

        return $this->essentialCookieNames;
    }

    private function deleteCookie(string $cookieName): void
    {
        $cookieMetadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());
        $this->cookieManager->deleteCookie($cookieName, $cookieMetadata);
    }

    /**
     * @return string[]
     */
    private function getExistCookies(): array
    {
        if (empty($this->existCookies)) {
            $this->existCookies = array_map(static function ($cookie) {
                return strtok(trim($cookie), '=');
            }, explode(';', $this->request->getHeader('cookie')));
        }

        return $this->existCookies;
    }
}
