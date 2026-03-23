<?php declare(strict_types=1);
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

namespace Hyva\ThemeFallback\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Hyva\ThemeFallback\Config\ThemeFallback as ThemeFallbackConfig;
use Hyva\ThemeFallback\Model\ThemeSwitch;
use Hyva\ThemeFallback\Service\FallbackPolicy;

class ThemeFallbackPlugin
{
    /**
     * @var Http|RequestInterface
     */
    private $request;

    /**
     * @var ThemeFallbackConfig
     */
    private $themeFallbackConfig;
    /**
     * @var ThemeSwitch
     */
    private $themeSwitch;
    /**
     * @var FallbackPolicy
     */
    private $fallbackPolicy;

    /**
     * @param RequestInterface $request
     * @param ThemeFallbackConfig $themeFallbackConfig
     * @param ThemeSwitch $themeSwitch
     * @param FallbackPolicy $fallbackPolicy
     */
    public function __construct(
        RequestInterface $request,
        ThemeFallbackConfig $themeFallbackConfig,
        ThemeSwitch $themeSwitch,
        FallbackPolicy $fallbackPolicy)
    {
        $this->request = $request;
        $this->themeFallbackConfig = $themeFallbackConfig;
        $this->themeSwitch = $themeSwitch;
        $this->fallbackPolicy = $fallbackPolicy;
    }

    /**
     * @param ActionInterface $subject
     */
    public function beforeExecute(ActionInterface $subject)
    {
        if ($this->isFallbackRequest()) {
            $this->themeSwitch->switchToFallback();
        }
    }

    /**
     * @return bool
     */
    protected function isFallbackRequest(): bool
    {
        if (!$this->themeFallbackConfig->isEnabled()) {
            return false;
        }

        $isFallbackRequest = false;
        foreach ($this->themeFallbackConfig->getListPartOfUrl() as $urlSegment) {
            if ($isFallbackRequest = $this->fallbackPolicy->isFallbackRequest($this->request, $urlSegment)) {
                break;
            }
        }
        return $isFallbackRequest;
    }
}
