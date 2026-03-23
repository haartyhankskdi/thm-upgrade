<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\EntityVersion;

use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class CookieVersionControlService
{
    public const FLAG_PREFIX = 'am_gdpr_cookie_last_update_';

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var DateTime
     */
    private $datetime;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        FlagManager $flagManager,
        DateTime $datetime,
        StoreManagerInterface $storeManager
    ) {
        $this->flagManager = $flagManager;
        $this->datetime = $datetime;
        $this->storeManager = $storeManager;
    }

    public function updateVersion(?int $storeId = null): void
    {
        $storeIds = $storeId === null ? $this->getAllStoreIds() : [(int)$storeId];

        foreach ($storeIds as $storeId) {
            $this->flagManager->saveFlag($this->getFlagCode($storeId), $this->datetime->gmtTimestamp());
        }
    }

    public function getVersion(int $storeId): int
    {
        $lastUpdate = (int)$this->flagManager->getFlagData($this->getFlagCode($storeId));
        $lastUpdateOnAllStores = (int)$this->flagManager->getFlagData($this->getFlagCode(0));

        return $lastUpdateOnAllStores > $lastUpdate
            ? $lastUpdateOnAllStores
            : $lastUpdate;
    }

    private function getAllStoreIds(): array
    {
        return array_map(function ($store) {
            return (int)$store->getId();
        }, $this->storeManager->getStores(true));
    }

    private function getFlagCode(int $storeId): string
    {
        return static::FLAG_PREFIX . $storeId;
    }
}
