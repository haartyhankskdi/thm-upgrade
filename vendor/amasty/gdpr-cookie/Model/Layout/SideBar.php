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
use Amasty\GdprCookie\Model\Cookie\CookieData;
use Amasty\GdprCookie\Utils\Reader\File;
use Magento\Store\Model\StoreManagerInterface;

class SideBar implements LayoutProcessorInterface
{
    private const CONTAINER_CLASS_NAME = 'modal-popup amgdprcookie-modal-container modal-slide _show';
    private const COOKIEBAR_TEMPLATE = 'Amasty_GdprFrontendUi::template/sidebar.html';
    private const COOKIEBAR_COMPONENT = 'Amasty_GdprFrontendUi/js/modal';

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

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CookieData
     */
    private $cookieData;

    public function __construct(
        Utils\CommonJsLayout $commonJsLayout,
        ConfigProvider $configProvider,
        File $fileReader,
        StoreManagerInterface $storeManager,
        CookieData $cookieData
    ) {
        $this->commonJsLayout = $commonJsLayout;
        $this->configProvider = $configProvider;
        $this->fileReader = $fileReader;
        $this->storeManager = $storeManager;
        $this->cookieData = $cookieData;
    }

    public function process(array $jsLayout): array
    {
        $commonJsLayout = $this->commonJsLayout->get();

        $jsLayout = [
            'config' => [
                'isPopup' => false,
                'isModal' => true,
                'className' => self::CONTAINER_CLASS_NAME,
                'buttons' => $this->getButtonsConfig(),
                'template' => $this->fileReader->getStaticFileContent(self::COOKIEBAR_TEMPLATE),
                'linkName' => __('More Information'),
                'groups' => $this->cookieData->getGroupData((int)$this->storeManager->getStore()->getId()),
                'cssConfig' => $this->getToggleSettings()
            ],
            'jsComponents' => [
                'components' => [
                    'gdpr-cookie-modal' => [
                        'component' => self::COOKIEBAR_COMPONENT
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
                'action' => 'saveCookie',
            ],
            [
                'label' => $this->configProvider->getSettingsButtonName() ?: __('Allow all Cookies'),
                'dataJs' => 'allow',
                'class' => '-settings',
                'action' => 'allowCookies'
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

    private function getToggleSettings(): array
    {
        return [
            'toggleActiveColor' => $this->configProvider->getSidebarToggleActiveStateColor(),
            'toggleInActiveColor' => $this->configProvider->getSidebarToggleInActiveStateColor()
        ];
    }
}
