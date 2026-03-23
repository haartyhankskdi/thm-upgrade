<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Model\Config\Source\CookiePolicyBarStyle;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    /**#@+
     * Constants defined for xpath of system configuration
     */
    public const COOKIE_POLICY_BAR = 'cookie_policy/bar';

    public const COOKIE_POLICY_BAR_TYPE = 'cookie_bar_customisation/cookies_bar_style';

    public const FIRST_VISIT_SHOW = 'cookie_policy/first_visit_show';

    public const COOKIE_POLICY_BAR_VISIBILITY = 'cookie_policy/bar_visibility';

    public const COOKIE_POLICY_BAR_COUNTRIES = 'cookie_policy/bar_countries';

    public const EU_COUNTRIES = 'general/country/eu_countries';

    public const LOG_GUEST = 'cookie_policy/log_guest';

    public const AUTO_CLEAR_LOG_DAYS = 'cookie_policy/auto_cleaning_days';

    public const COOKIE_BAR_LOCATION = 'cookie_bar_customisation/classic_bar/cookies_bar_location';

    public const SIDEBAR_GROUP_TITLE_TEXT_COLOR = 'cookie_bar_customisation/sidebar/group_title_text_color';

    public const SIDEBAR_GROUP_DESCRIPTION_TEXT_COLOR = 'cookie_bar_customisation/sidebar/group_desc_text_color';

    public const TYPE_POPUP = 'cookie_bar_customisation/popup/';

    public const TYPE_SIDEBAR = 'cookie_bar_customisation/sidebar/';

    public const TYPE_CLASSIC = 'cookie_bar_customisation/classic_bar/';

    public const NOTIFICATION_TEXT = 'notification_text';

    public const BACKGROUND_COLOR = 'background_color';

    public const LINKS_COLOR = 'links_color';

    public const POLICY_TEXT_COLOR = 'policy_text_color';

    public const ACCEPT_BUTTON_TEXT = 'accept_button/button_text';

    public const ACCEPT_BUTTON_ORDER = 'accept_button/button_order';

    public const ACCEPT_BUTTON_COLOR = 'accept_button/button_color';

    public const ACCEPT_BUTTON_COLOR_HOVER = 'accept_button/button_color_hover';

    public const ACCEPT_TEXT_COLOR = 'accept_button/text_color';

    public const ACCEPT_TEXT_COLOR_HOVER = 'accept_button/text_color_hover';

    public const SETTINGS_BUTTON_TEXT = 'settings_button/button_text';

    public const SETTINGS_BUTTON_ORDER = 'settings_button/button_order';

    public const SETTINGS_BUTTON_COLOR = 'settings_button/button_color';

    public const SETTINGS_BUTTON_COLOR_HOVER = 'settings_button/button_color_hover';

    public const SETTINGS_TEXT_COLOR = 'settings_button/text_color';

    public const SETTINGS_TEXT_COLOR_HOVER = 'settings_button/text_color_hover';

    public const DECLINE_BUTTON_TEXT = 'decline_button/button_text';

    public const DECLINE_BUTTON_ORDER = 'decline_button/button_order';

    public const DECLINE_BUTTON_COLOR = 'decline_button/button_color';

    public const DECLINE_BUTTON_COLOR_HOVER = 'decline_button/button_color_hover';

    public const DECLINE_TEXT_COLOR = 'decline_button/text_color';

    public const DECLINE_TEXT_COLOR_HOVER = 'decline_button/text_color_hover';

    public const DECLINE_ENABLE = 'decline_button/enable';

    public const COOKIE_SETTINGS_BAR_BACKGROUND_COLOR = 'cookie_settings_bar_customisation/background_color';

    public const COOKIE_SETTINGS_BAR_GROUP_TITLE_TEXT_COLOR
        = 'cookie_settings_bar_customisation/group_title_text_color';

    public const COOKIE_SETTINGS_BAR_GROUP_DESCRIPTION_TEXT_COLOR
        = 'cookie_settings_bar_customisation/group_desc_text_color';

    public const COOKIE_SETTINGS_BAR_GROUP_LINKS_COLOR = 'cookie_settings_bar_customisation/links_color';

    public const COOKIE_SETTINGS_BAR_DONE_BUTTON_TEXT = 'cookie_settings_bar_customisation/done_button/button_text';

    public const COOKIE_SETTINGS_BAR_DONE_BUTTON_COLOR = 'cookie_settings_bar_customisation/done_button/button_color';

    public const COOKIE_SETTINGS_BAR_DONE_BUTTON_COLOR_HOVER
        = 'cookie_settings_bar_customisation/done_button/button_color_hover';

    public const COOKIE_SETTINGS_BAR_DONE_BUTTON_TEXT_COLOR
        = 'cookie_settings_bar_customisation/done_button/text_color';

    public const COOKIE_SETTINGS_BAR_DONE_BUTTON_TEXT_COLOR_HOVER
        = 'cookie_settings_bar_customisation/done_button/text_color_hover';
    public const COOKIE_SETTINGS_BAR_TOGGLE_ACTIVE_STATE_COLOR
        = 'cookie_settings_bar_customisation/toggle_active_color';
    public const COOKIE_SETTINGS_BAR_TOGGLE_INACTIVE_STATE_COLOR
        = 'cookie_settings_bar_customisation/toggle_inactive_color';
    public const SIDEBAR_TOGGLE_ACTIVE_STATE_COLOR = 'cookie_bar_customisation/sidebar/toggle_active_color';
    public const SIDEBAR_TOGGLE_INACTIVE_STATE_COLOR = 'cookie_bar_customisation/sidebar/toggle_inactive_color';
    public const COOKIE_INFORMATION_BAR_BACKGROUND_COLOR = 'cookie_information_bar_customisation/background_color';

    public const COOKIE_INFORMATION_BAR_TITLE_COLOR = 'cookie_information_bar_customisation/title_color';

    public const COOKIE_INFORMATION_BAR_DESCRIPTION_COLOR = 'cookie_information_bar_customisation/description_color';

    public const COOKIE_INFORMATION_BAR_TABLE_HEADER_COLOR = 'cookie_information_bar_customisation/table_header_color';
    public const COOKIE_INFORMATION_BAR_TABLE_CONTENT_COLOR
        = 'cookie_information_bar_customisation/table_content_color';

    public const COOKIE_INFORMATION_BAR_DONE_BUTTON_TEXT
        = 'cookie_information_bar_customisation/done_button/button_text';

    public const COOKIE_INFORMATION_BAR_DONE_BUTTON_COLOR
        = 'cookie_information_bar_customisation/done_button/button_color';

    public const COOKIE_INFORMATION_BAR_DONE_BUTTON_COLOR_HOVER
        = 'cookie_information_bar_customisation/done_button/button_color_hover';

    public const COOKIE_INFORMATION_BAR_DONE_BUTTON_TEXT_COLOR
        = 'cookie_information_bar_customisation/done_button/text_color';

    public const COOKIE_INFORMATION_BAR_DONE_BUTTON_TEXT_COLOR_HOVER
        = 'cookie_information_bar_customisation/done_button/text_color_hover';

    /**#@-*/

    /**
     * @var string
     */
    protected $pathPrefix = 'amasty_gdprcookie/';

    /**
     * @var string[]
     */
    protected $customisationTypes = [
        CookiePolicyBarStyle::CONFIRMATION => self::TYPE_CLASSIC,
        CookiePolicyBarStyle::CONFIRMATION_MODAL => self::TYPE_SIDEBAR,
        CookiePolicyBarStyle::CONFIRMATION_POPUP => self::TYPE_POPUP
    ];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $customisationTypes = []
    ) {
        parent::__construct($scopeConfig);
        $this->customisationTypes = array_merge($this->customisationTypes, $customisationTypes);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isCookieBarEnabled($scopeCode = null)
    {
        return (bool)$this->getValue(self::COOKIE_POLICY_BAR, $scopeCode);
    }

    /**
     * @param null $scopeCode
     * @return int
     */
    public function getCookiePrivacyBarType($scopeCode = null)
    {
        return (int)$this->getValue(self::COOKIE_POLICY_BAR_TYPE, $scopeCode);
    }

    /**
     * @param null|int $scopeCode
     *
     * @return int
     */
    public function getFirstVisitShow($scopeCode = null)
    {
        return (int)$this->getValue(self::FIRST_VISIT_SHOW, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return string
     */
    public function getCookiePolicyBarVisibility($scopeCode = null)
    {
        return (int)$this->getValue(self::COOKIE_POLICY_BAR_VISIBILITY, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return array
     */
    public function getCookiePolicyBarCountriesCodes($scopeCode = null)
    {
        $countriesCodes = (string)$this->getValue(self::COOKIE_POLICY_BAR_COUNTRIES, $scopeCode);

        return array_filter(explode(',', $countriesCodes));
    }

    /**
     * @return array
     */
    public function getEuCountriesCodes()
    {
        $countriesCodes = (string)$this->scopeConfig->getValue(self::EU_COUNTRIES);

        return array_filter(explode(',', $countriesCodes));
    }

    /**
     * @param null|string $scopeCode
     *
     * @return string
     */
    public function getNotificationText($scopeCode = null)
    {
        return (string)$this->getValue($this->getCustomisationType() . self::NOTIFICATION_TEXT, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getBackgroundColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::BACKGROUND_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getPolicyTextColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::POLICY_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getLinksColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::LINKS_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAcceptButtonName($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::ACCEPT_BUTTON_TEXT, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAcceptButtonOrder($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::ACCEPT_BUTTON_ORDER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAcceptButtonColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::ACCEPT_BUTTON_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAcceptButtonColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::ACCEPT_BUTTON_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAcceptTextColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::ACCEPT_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAcceptTextColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::ACCEPT_TEXT_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getSettingsButtonName($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::SETTINGS_BUTTON_TEXT, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getSettingsButtonOrder($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::SETTINGS_BUTTON_ORDER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getSettingsButtonColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::SETTINGS_BUTTON_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getSettingsButtonColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::SETTINGS_BUTTON_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getSettingsTextColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::SETTINGS_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getSettingsTextColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::SETTINGS_TEXT_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|int $scopeCode
     *
     * @return int
     */
    public function getDeclineEnabled($scopeCode = null)
    {
        return (int)$this->getValue($this->getCustomisationType() . self::DECLINE_ENABLE, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDeclineButtonName($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::DECLINE_BUTTON_TEXT, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDeclineButtonOrder($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::DECLINE_BUTTON_ORDER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDeclineButtonColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::DECLINE_BUTTON_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDeclineButtonColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::DECLINE_BUTTON_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDeclineTextColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::DECLINE_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDeclineTextColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::DECLINE_TEXT_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getTitleTextColor($scopeCode = null)
    {
        return $this->getValue(self::SIDEBAR_GROUP_TITLE_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDescriptionTextColor($scopeCode = null)
    {
        return $this->getValue(self::SIDEBAR_GROUP_DESCRIPTION_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getBarLocation($scopeCode = null)
    {
        return $this->getValue(self::COOKIE_BAR_LOCATION, $scopeCode);
    }

    /**
     * @param null|int $scopeCode
     *
     * @return int
     */
    public function getAutoCleaningDays($scopeCode = null)
    {
        return (int)$this->getValue(self::AUTO_CLEAR_LOG_DAYS, $scopeCode);
    }

    /**
     * @return string
     */
    public function getCustomisationType()
    {
        foreach ($this->customisationTypes as $customisationType => $path) {
            if ($this->getCookiePrivacyBarType() == $customisationType) {
                return $path;
            }
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isLogGuest()
    {
        return (bool)$this->getValue(self::LOG_GUEST);
    }

    public function getCookieSettingsBarBackgroundColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_SETTINGS_BAR_BACKGROUND_COLOR, $scopeCode);
    }

    public function getCookieSettingsBarGroupTitleTextColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_SETTINGS_BAR_GROUP_TITLE_TEXT_COLOR, $scopeCode);
    }

    public function getCookieSettingsBarGroupDescriptionTextColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_SETTINGS_BAR_GROUP_DESCRIPTION_TEXT_COLOR, $scopeCode);
    }

    public function getCookieSettingsBarGroupLinksColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_SETTINGS_BAR_GROUP_LINKS_COLOR, $scopeCode);
    }

    public function getCookieSettingsBarDoneButtonText(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_SETTINGS_BAR_DONE_BUTTON_TEXT, $scopeCode);
    }

    public function getCookieSettingsBarDoneButtonColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_SETTINGS_BAR_DONE_BUTTON_COLOR, $scopeCode);
    }

    public function getCookieSettingsBarDoneButtonColorHover(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_SETTINGS_BAR_DONE_BUTTON_COLOR_HOVER, $scopeCode);
    }

    public function getCookieSettingsBarDoneButtonTextColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_SETTINGS_BAR_DONE_BUTTON_TEXT_COLOR, $scopeCode);
    }

    public function getCookieSettingsBarDoneButtonTextColorHover(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_SETTINGS_BAR_DONE_BUTTON_TEXT_COLOR_HOVER, $scopeCode);
    }

    public function getCookieSettingsBarToggleActiveStateColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_SETTINGS_BAR_TOGGLE_ACTIVE_STATE_COLOR, $scopeCode);
    }

    public function getCookieSettingsBarToggleInActiveStateColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_SETTINGS_BAR_TOGGLE_INACTIVE_STATE_COLOR, $scopeCode);
    }

    public function getSidebarToggleActiveStateColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::SIDEBAR_TOGGLE_ACTIVE_STATE_COLOR, $scopeCode);
    }

    public function getSidebarToggleInActiveStateColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::SIDEBAR_TOGGLE_INACTIVE_STATE_COLOR, $scopeCode);
    }

    public function getCookieInformationBarBackgroundColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_INFORMATION_BAR_BACKGROUND_COLOR, $scopeCode);
    }

    public function getCookieInformationBarTitleColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_INFORMATION_BAR_TITLE_COLOR, $scopeCode);
    }

    public function getCookieInformationBarDescriptionColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_INFORMATION_BAR_DESCRIPTION_COLOR, $scopeCode);
    }

    public function getCookieInformationBarTableHeaderColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_INFORMATION_BAR_TABLE_HEADER_COLOR, $scopeCode);
    }

    public function getCookieInformationBarTableContentColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_INFORMATION_BAR_TABLE_CONTENT_COLOR, $scopeCode);
    }

    public function getCookieInformationBarDoneButtonText(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_INFORMATION_BAR_DONE_BUTTON_TEXT, $scopeCode);
    }

    public function getCookieInformationBarDoneButtonColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_INFORMATION_BAR_DONE_BUTTON_COLOR, $scopeCode);
    }

    public function getCookieInformationBarDoneButtonColorHover(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_INFORMATION_BAR_DONE_BUTTON_COLOR_HOVER, $scopeCode);
    }

    public function getCookieInformationBarDoneButtonTextColor(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_INFORMATION_BAR_DONE_BUTTON_TEXT_COLOR, $scopeCode);
    }

    public function getCookieInformationBarDoneButtonTextColorHover(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COOKIE_INFORMATION_BAR_DONE_BUTTON_TEXT_COLOR_HOVER, $scopeCode);
    }
}
