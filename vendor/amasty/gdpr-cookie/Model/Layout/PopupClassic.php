<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Layout;

use Amasty\GdprCookie\Model\Config\Source\CookiePolicyBarStyle;
use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Utils\Reader\File;

class PopupClassic implements LayoutProcessorInterface
{
    private const CONTAINER_CLASS_NAME = 'amgdprjs-bar-template';
    private const COOKIEBAR_TEMPLATE = 'Amasty_GdprFrontendUi::template/cookiebar.html';
    private const COOKIEBAR_COMPONENT = 'Amasty_GdprFrontendUi/js/cookies';

    /**
     * @var Utils\CommonJsLayout
     */
    private $commonJsLayout;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var File
     */
    private $fileReader;

    public function __construct(
        Utils\CommonJsLayout $commonJsLayout,
        ConfigProvider $configProvider,
        File $fileReader
    ) {
        $this->commonJsLayout = $commonJsLayout;
        $this->configProvider = $configProvider;
        $this->fileReader = $fileReader;
    }

    public function process(array $jsLayout): array
    {
        $commonJsLayout = $this->commonJsLayout->get();

        $jsLayout = [
            'config' => [
                'isPopup' => $this->configProvider->getCookiePrivacyBarType()
                    == CookiePolicyBarStyle::CONFIRMATION_POPUP,
                'isModal' => false,
                'className' => self::CONTAINER_CLASS_NAME,
                'buttons' => $this->getButtonsConfig(),
                'template' => $this->fileReader->getStaticFileContent(self::COOKIEBAR_TEMPLATE)
            ],
            'jsComponents' => [
                'components' => [
                    'gdpr-cookie-modal' => [
                        'component' => self::COOKIEBAR_COMPONENT,
                        'barType' => $this->configProvider->getCookiePrivacyBarType(),
                        'barLocation' => (int)$this->configProvider->getBarLocation()
                    ]
                ]
            ]
        ];

        return array_merge_recursive($commonJsLayout, $jsLayout);
    }

    private function getButtonsConfig(): array
    {
        $buttons = [
            [
                'label'  => $this->configProvider->getAcceptButtonName() ? : __('Accept Cookies'),
                'dataJs' => 'accept',
                'class'  => '-allow -save',
                'action' => 'allowCookies',
            ],
            [
                'label'  => $this->configProvider->getSettingsButtonName() ? : __('Custom Settings'),
                'dataJs' => 'settings',
                'class'  => '-settings'
            ]
        ];

        if ($this->configProvider->getDeclineEnabled()) {
            $buttons[] = [
                'label'  => $this->configProvider->getDeclineButtonName() ? : __('Decline Cookies'),
                'dataJs' => 'decline',
                'class'  => '-decline',
                'action' => 'declineCookie'
            ];
        }

        return $buttons;
    }
}
