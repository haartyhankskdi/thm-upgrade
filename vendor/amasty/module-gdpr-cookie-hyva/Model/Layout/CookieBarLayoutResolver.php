<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Hyva Compatibility (System)
 */

namespace Amasty\GdprCookieHyva\Model\Layout;

use Amasty\GdprCookie\Model\ConfigProvider;
use Hyva\Theme\Service\CurrentTheme;
use Magento\Framework\View\Layout;

class CookieBarLayoutResolver
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CurrentTheme
     */
    private $currentTheme;

    /**
     * @var array
     */
    private $layoutHandlerMap;

    public function __construct(
        ConfigProvider $configProvider,
        CurrentTheme $currentTheme,
        array $layoutHandlerMap = []
    ) {
        $this->configProvider = $configProvider;
        $this->currentTheme = $currentTheme;
        $this->layoutHandlerMap = $layoutHandlerMap;
    }

    public function resolve(Layout $layout): void
    {
        if ($this->currentTheme->isHyva() && $this->configProvider->isCookieBarEnabled()) {
            $barType = $this->configProvider->getCookiePrivacyBarType();
            $handlerName = $this->layoutHandlerMap[$barType] ?? null;

            if ($handlerName) {
                $layout->getUpdate()->addHandle($handlerName);
            }
        }
    }
}
