<?php declare(strict_types=1);
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

namespace Hyva\ThemeFallback\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ThemeFallback
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'hyva_theme_fallback/general/enable',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getThemeFullPath(): string
    {
        return $this->scopeConfig->getValue(
            'hyva_theme_fallback/general/theme_full_path',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getListPartOfUrl(): array
    {
        $urlPaths = [];
        $configJson = $this->scopeConfig->getValue(
            'hyva_theme_fallback/general/list_part_of_url',
            ScopeInterface::SCOPE_STORE
        );
        $config = json_decode($configJson, true) ?: [];
        foreach ($config as $item) {
            if (isset($item['path']) && $item['path']) {
                $urlPaths[] = $item['path'];
            }
        }

        return $urlPaths;
    }
}
