<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Consent Mode
 */

namespace Amasty\GoogleConsentMode\ViewModel;

use Amasty\Base\Model\Serializer;
use Amasty\GoogleConsentMode\Model\ConfigProvider;
use Amasty\GoogleConsentMode\Model\Frontend\HeaderScriptPersist;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManager;

class GtmViewModel implements ArgumentInterface
{
    public const COOKIE_MODULE = 'Amasty_GdprCookie';

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var HeaderScriptPersist
     */
    private HeaderScriptPersist $headerScriptPersist;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var StoreManager
     */
    private StoreManager $storeManager;

    /**
     * @var Manager
     */
    private Manager $moduleManager;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ConfigProvider $configProvider,
        HeaderScriptPersist $headerScriptPersist,
        Serializer $serializer,
        StoreManager $storeManager,
        Manager $moduleManager,
        $nonceProvider = null //@backward compatibility
    ) {
        $this->configProvider = $configProvider;
        $this->headerScriptPersist = $headerScriptPersist;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->moduleManager = $moduleManager;
    }

    public function isConsentModeEnabled(): bool
    {
        return $this->configProvider->isConsentModeEnabled();
    }

    public function getConsentTypesData(): string
    {
        $result = [];
        $types = $this->serializer->unserialize($this->configProvider->getConsentTypes());
        foreach ($types as $type) {
            $result[$type['consent_type']] = ['default' => $type['default_status'], 'group' => $type['cookie_group']];
        }

        return $this->serializer->serialize($result);
    }

    public function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    public function replaceTags(string $content): string
    {
        if ($this->headerScriptPersist->needToReplace()) {
            $content = $this->headerScriptPersist->changeScriptTags($content);
        }

        return $content;
    }

    public function isGdprModuleEnabled(): bool
    {
        return $this->moduleManager->isEnabled(self::COOKIE_MODULE);
    }

    /**
     * @deprecated backward compatibility
     * @see self::replaceTags()
     */
    public function getTagName(): string
    {
        return $this->headerScriptPersist->getTagName();
    }

    /**
     * @deprecated backward compatibility
     * @see self::replaceTags()
     */
    public function getOpenTag(): string
    {
        return $this->headerScriptPersist->getOpenTag();
    }

    /**
     * @deprecated backward compatibility
     * @see self::replaceTags()
     */
    public function getCloseTag(): string
    {
        return $this->headerScriptPersist->getCloseTag();
    }

    /**
     * @deprecated backward compatibility
     * @see self::replaceTags()
     */
    public function getAttributes(): array
    {
        return [];
    }
}
