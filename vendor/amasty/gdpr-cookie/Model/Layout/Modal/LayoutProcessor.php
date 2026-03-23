<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Layout\Modal;

use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\Layout\LayoutProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;

class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var string
     */
    private $locationPathInLayout;

    /**
     * @var string
     */
    private $componentJs;

    /**
     * @var string
     */
    private $componentName;

    public function __construct(
        ConfigProvider $configProvider,
        ArrayManager $arrayManager,
        string $componentName,
        string $componentJs,
        string $locationPathInLayout
    ) {
        $this->configProvider = $configProvider;
        $this->arrayManager = $arrayManager;
        $this->componentName = $componentName;
        $this->componentJs = $componentJs;
        $this->locationPathInLayout = $locationPathInLayout;
    }

    public function process(array $jsLayout): array
    {
        $mainModal = $this->arrayManager->get($this->locationPathInLayout, $jsLayout);
        if (!$mainModal) {
            return $jsLayout;
        }

        $component = [
            $this->componentName => [
                'component' => $this->componentJs,
            ]
        ];

        if ($settings = $this->getSettings()) {
            $component[$this->componentName]['settings'] = $settings;
        }

        $mainModal['children'][$this->componentName] = $component[$this->componentName];

        return $this->arrayManager->set($this->locationPathInLayout, $jsLayout, $mainModal);
    }

    protected function getConfigProvider(): ConfigProvider
    {
        return $this->configProvider;
    }

    protected function getSettings(): array
    {
        return [];
    }
}
