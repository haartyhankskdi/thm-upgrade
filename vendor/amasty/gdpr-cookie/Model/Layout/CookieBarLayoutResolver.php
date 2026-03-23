<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Layout;

use Amasty\GdprCookie\Model\ConfigProvider;

/**
 * Resolves Cookie Bar Layout Processor by current Cookie Bar Type and proceeds processing to it.
 * To provide additional js layout processing add it directly to \Amasty\GdprCookie\Block\Consent
 */
class CookieBarLayoutResolver implements LayoutProcessorInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    private $cookieBarLayouts;

    public function __construct(
        ConfigProvider $configProvider,
        array $cookieBarLayouts = []
    ) {
        $this->configProvider = $configProvider;
        $this->initCookieBarLayouts($cookieBarLayouts);
    }

    public function process(array $jsLayout): array
    {
        if ($cookieBarLayoutProcessor = $this->get()) {
            $jsLayout = $cookieBarLayoutProcessor->process($jsLayout);
        }

        return $jsLayout;
    }

    public function _resetState(): void
    {
        $this->cookieBarLayouts = [];
    }

    private function get(): ?LayoutProcessorInterface
    {
        $barType = $this->configProvider->getCookiePrivacyBarType();

        return $this->cookieBarLayouts[$barType] ?? null;
    }

    private function initCookieBarLayouts(array $cookieBarLayouts): void
    {
        foreach ($cookieBarLayouts as $layoutType => $layoutObject) {
            if (!$layoutObject instanceof LayoutProcessorInterface) {
                throw new \LogicException(
                    sprintf('Cookie Bar Layout Processor must implement %s', LayoutProcessorInterface::class)
                );
            }
            $this->cookieBarLayouts[$layoutType] = $layoutObject;
        }
    }
}
