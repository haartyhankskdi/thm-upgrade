<?php declare(strict_types=1);
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

namespace Hyva\ThemeFallback\Model;

use Hyva\ThemeFallback\Config\ThemeFallback as ThemeFallbackConfig;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface as ThemeProviderInterface;
use Magento\Framework\View\Design\ThemeInterface as ThemeInterface;
use Magento\Framework\View\DesignInterface as DesignInterface;

class ThemeSwitch
{
    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;
    /**
     * @var DesignInterface
     */
    private $design;
    /**
     * @var ThemeFallbackConfig
     */
    private $config;

    public function __construct(
        ThemeProviderInterface $themeProvider,
        DesignInterface $design,
        ThemeFallbackConfig $config)
    {
        $this->themeProvider = $themeProvider;
        $this->design = $design;
        $this->config = $config;
    }

    public function switchToFallback(): void
    {
        $this->design->setDesignTheme($this->getTheme());
    }

    private function getTheme(): ThemeInterface
    {
        return $this->themeProvider->getThemeByFullPath($this->config->getThemeFullPath());
    }
}
