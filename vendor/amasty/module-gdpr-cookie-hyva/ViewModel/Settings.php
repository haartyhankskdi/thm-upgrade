<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Hyva Compatibility (System)
 */

namespace Amasty\GdprCookieHyva\ViewModel;

use Amasty\GdprCookie\Model\ConfigProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Settings implements ArgumentInterface
{
    private const DEFAULT_BAR_BACKGROUND = '#fff';
    private const DEFAULT_BAR_LINK_COLOR = '#326ed1';
    private const DEFAULT_BAR_TEXT_COLOR = '#374151';
    private const DEFAULT_BUTTON_BACKGROUND = '#337ab7';
    private const DEFAULT_BUTTON_TEXT_COLOR = '#fff';
    private const DEFAULT_TOGGLE_ACTIVE_STATE_COLOR = '#2563eb';
    private const DEFAULT_TOGGLE_INACTIVE_STATE_COLOR = '#fff';
    private const DEFAULT_DONE_BUTTON_BACKGROUND = '#1d4ed8';
    private const DEFAULT_COOKIE_SETTINGS_BAR_BACKGROUND_COLOR = '#fff';
    private const DEFAULT_COOKIE_SETTINGS_BAR_GROUP_TITLE_TEXT_COLOR = '#000';
    private const DEFAULT_COOKIE_SETTINGS_BAR_GROUP_DESCRIPTION_TEXT_COLOR = '#000';
    private const DEFAULT_COOKIE_SETTINGS_BAR_GROUP_LINKS_COLOR = '#326ed1';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function getPolicyTextColor(): string
    {
        return $this->configProvider->getPolicyTextColor() ?: self::DEFAULT_BAR_TEXT_COLOR;
    }

    public function getBackgroundColor(): string
    {
        return $this->configProvider->getBackgroundColor() ?: self::DEFAULT_BAR_BACKGROUND;
    }

    public function getAcceptButtonColor(): string
    {
        return $this->configProvider->getAcceptButtonColor() ?: self::DEFAULT_BUTTON_BACKGROUND;
    }

    public function getAcceptButtonColorHover(): string
    {
        return $this->configProvider->getAcceptButtonColorHover() ?: self::DEFAULT_BUTTON_BACKGROUND;
    }

    public function getAcceptTextColor(): string
    {
        return $this->configProvider->getAcceptTextColor() ?: self::DEFAULT_BUTTON_TEXT_COLOR;
    }

    public function getAcceptTextColorHover(): string
    {
        return $this->configProvider->getAcceptTextColorHover() ?: self::DEFAULT_BUTTON_TEXT_COLOR;
    }

    public function getAcceptButtonOrder(): int
    {
        return (int)$this->configProvider->getAcceptButtonOrder();
    }

    public function getLinksColor(): string
    {
        return $this->configProvider->getLinksColor() ?: self::DEFAULT_BAR_LINK_COLOR;
    }

    public function getSettingsButtonColor(): string
    {
        return $this->configProvider->getSettingsButtonColor() ?: self::DEFAULT_BUTTON_BACKGROUND;
    }

    public function getSettingsButtonColorHover(): string
    {
        return $this->configProvider->getSettingsButtonColorHover() ?: self::DEFAULT_BUTTON_BACKGROUND;
    }

    public function getSettingsTextColor(): string
    {
        return $this->configProvider->getSettingsTextColor() ?: self::DEFAULT_BUTTON_TEXT_COLOR;
    }

    public function getSettingsTextColorHover(): string
    {
        return $this->configProvider->getSettingsTextColorHover() ?: self::DEFAULT_BUTTON_TEXT_COLOR;
    }

    public function getSettingsButtonOrder(): int
    {
        return (int)$this->configProvider->getSettingsButtonOrder();
    }

    public function getTitleTextColor(): string
    {
        return $this->configProvider->getTitleTextColor() ?: self::DEFAULT_BAR_TEXT_COLOR;
    }

    public function getDescriptionTextColor(): string
    {
        return $this->configProvider->getDescriptionTextColor() ?: self::DEFAULT_BAR_TEXT_COLOR;
    }

    public function getDeclineButtonColor(): string
    {
        return $this->configProvider->getDeclineButtonColor() ?: self::DEFAULT_BUTTON_BACKGROUND;
    }

    public function getDeclineButtonColorHover(): string
    {
        return $this->configProvider->getDeclineButtonColorHover() ?: self::DEFAULT_BUTTON_BACKGROUND;
    }

    public function getDeclineTextColor(): string
    {
        return $this->configProvider->getDeclineTextColor() ?: self::DEFAULT_BUTTON_TEXT_COLOR;
    }

    public function getDeclineTextColorHover(): string
    {
        return $this->configProvider->getDeclineTextColorHover() ?: self::DEFAULT_BUTTON_TEXT_COLOR;
    }

    public function getDeclineButtonOrder(): int
    {
        return (int)$this->configProvider->getDeclineButtonOrder();
    }

    public function getFirstShowProcess(): int
    {
        return $this->configProvider->getFirstVisitShow();
    }

    public function getToggleActiveStateColor(): string
    {
        return $this->configProvider->getCookieSettingsBarToggleActiveStateColor()
            ?: self::DEFAULT_TOGGLE_ACTIVE_STATE_COLOR;
    }

    public function getToggleInActiveStateColor(): string
    {
        return $this->configProvider->getCookieSettingsBarToggleInActiveStateColor()
            ?: self::DEFAULT_TOGGLE_INACTIVE_STATE_COLOR;
    }

    public function getSidebarToggleActiveStateColor(): string
    {
        return $this->configProvider->getSidebarToggleActiveStateColor()
            ?: self::DEFAULT_TOGGLE_ACTIVE_STATE_COLOR;
    }

    public function getSidebarToggleInActiveStateColor(): string
    {
        return $this->configProvider->getSidebarToggleInActiveStateColor()
            ?: self::DEFAULT_TOGGLE_INACTIVE_STATE_COLOR;
    }

    public function getCookieInformationBarBackgroundColor(): string
    {
        return $this->configProvider->getCookieInformationBarBackgroundColor()
            ?: self::DEFAULT_BAR_BACKGROUND;
    }

    public function getCookieInformationBarTitleColor(): string
    {
        return $this->configProvider->getCookieInformationBarTitleColor()
            ?: self::DEFAULT_BAR_TEXT_COLOR;
    }

    public function getCookieInformationBarDescriptionColor(): string
    {
        return $this->configProvider->getCookieInformationBarDescriptionColor()
            ?: self::DEFAULT_BAR_TEXT_COLOR;
    }

    public function getCookieInformationBarTableHeaderColor(): string
    {
        return $this->configProvider->getCookieInformationBarTableHeaderColor()
            ?: self::DEFAULT_BAR_TEXT_COLOR;
    }

    public function getCookieInformationBarTableContentColor(): string
    {
        return $this->configProvider->getCookieInformationBarTableContentColor()
            ?: self::DEFAULT_BAR_TEXT_COLOR;
    }

    public function getCookieInformationBarDoneButtonText(): string
    {
        return $this->configProvider->getCookieInformationBarDoneButtonText()
            ?: __('Done')->render();
    }

    public function getCookieInformationBarDoneButtonColor(): string
    {
        return $this->configProvider->getCookieInformationBarDoneButtonColor()
            ?: self::DEFAULT_DONE_BUTTON_BACKGROUND;
    }

    public function getCookieInformationBarDoneButtonColorHover(): string
    {
        return $this->configProvider->getCookieInformationBarDoneButtonColorHover()
            ?: self::DEFAULT_DONE_BUTTON_BACKGROUND;
    }

    public function getCookieInformationBarDoneButtonTextColor(): string
    {
        return $this->configProvider->getCookieInformationBarDoneButtonTextColor()
            ?: self::DEFAULT_BUTTON_TEXT_COLOR;
    }

    public function getCookieInformationBarDoneButtonTextColorHover(): string
    {
        return $this->configProvider->getCookieInformationBarDoneButtonTextColorHover()
            ?: self::DEFAULT_BUTTON_TEXT_COLOR;
    }

    public function getCookieSettingsBarDoneButtonText(): string
    {
        return $this->configProvider->getCookieSettingsBarDoneButtonText()
            ?: __('Done')->render();
    }

    public function getCookieSettingsBarDoneButtonColor(): string
    {
        return $this->configProvider->getCookieSettingsBarDoneButtonColor()
            ?: self::DEFAULT_DONE_BUTTON_BACKGROUND;
    }

    public function getCookieSettingsBarDoneButtonColorHover(): string
    {
        return $this->configProvider->getCookieSettingsBarDoneButtonColorHover()
            ?: self::DEFAULT_DONE_BUTTON_BACKGROUND;
    }

    public function getCookieSettingsBarDoneButtonTextColor(): string
    {
        return $this->configProvider->getCookieSettingsBarDoneButtonTextColor()
            ?: self::DEFAULT_BUTTON_TEXT_COLOR;
    }

    public function getCookieSettingsBarDoneButtonTextColorHover(): string
    {
        return $this->configProvider->getCookieSettingsBarDoneButtonTextColorHover()
            ?: self::DEFAULT_BUTTON_TEXT_COLOR;
    }

    public function getCookieSettingsBarBackgroundColor(): string
    {
        return $this->configProvider->getCookieSettingsBarBackgroundColor()
            ?: self::DEFAULT_COOKIE_SETTINGS_BAR_BACKGROUND_COLOR;
    }

    public function getCookieSettingsBarGroupTitleTextColor(): string
    {
        return $this->configProvider->getCookieSettingsBarGroupTitleTextColor()
            ?: self::DEFAULT_COOKIE_SETTINGS_BAR_GROUP_TITLE_TEXT_COLOR;
    }

    public function getCookieSettingsBarGroupDescriptionTextColor(): string
    {
        return $this->configProvider->getCookieSettingsBarGroupDescriptionTextColor()
            ?: self::DEFAULT_COOKIE_SETTINGS_BAR_GROUP_DESCRIPTION_TEXT_COLOR;
    }

    public function getCookieSettingsBarGroupLinksColor(): string
    {
        return $this->configProvider->getCookieSettingsBarGroupLinksColor()
            ?: self::DEFAULT_COOKIE_SETTINGS_BAR_GROUP_LINKS_COLOR;
    }
}
