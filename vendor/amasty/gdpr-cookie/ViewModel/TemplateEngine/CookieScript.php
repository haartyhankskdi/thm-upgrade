<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\ViewModel\TemplateEngine;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Api\Data\CookieInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class CookieScript
{
    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int|null
     */
    private $storeId = null;

    /**
     * @var array|null
     */
    private $cookies = null;

    public function __construct(
        CookieManagementInterface $cookieManagement,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->cookieManagement = $cookieManagement;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function getGroupIdByCookieNames(array $cookieNames): ?int
    {
        $matchedCookies = array_intersect_key($this->getCookies($this->getStoreId()), array_flip($cookieNames));
        $groupIds = array_unique($matchedCookies);

        if (count($groupIds) > 1) {
            $this->logger->error(__(
                'Amasty GDPR cookie error: cookies "%1" are in different groups "%2".',
                implode(', ', $cookieNames),
                implode(', ', $groupIds)
            ));

            return null;
        }

        return array_shift($groupIds);
    }

    public function _resetState(): void
    {
        $this->cookies = null;
        $this->storeId = null;
    }

    private function getCookies(int $storeId): array
    {
        if ($this->cookies === null) {
            /** @var CookieInterface $cookie */
            foreach ($this->cookieManagement->getCookies($storeId) as $cookie) {
                if ($cookie->isEnabled() && $cookie->getGroupId()) {
                    $this->cookies[$cookie->getName()] = (int)$cookie->getGroupId();
                }
            }
        }

        return $this->cookies;
    }

    private function getStoreId(): int
    {
        if ($this->storeId !== null) {
            return $this->storeId;
        }

        return $this->storeId = (int)$this->storeManager->getStore()->getId();
    }
}
