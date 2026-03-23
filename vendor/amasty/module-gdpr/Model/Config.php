<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const PATH_PREFIX = 'amasty_gdpr';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    public const PRIVACY_CHECKBOX_EEA_COUNTRIES = 'privacy_checkbox/eea_countries';

    public const MODULE_ENABLED = 'general/enabled';
    public const DISPLAY_PP_POPUP = 'general/display_pp_popup';
    public const COUNTRY_RESTRICTIONS = 'general/country_restrictions';
    public const COUNTRIES = 'general/countries';
    public const LOG_GUEST = 'general/log_guest';
    public const EU_COUNTRIES = 'general/country/eu_countries';
    public const AUTO_CLEANING = 'general/auto_cleaning';
    public const AUTO_CLEANING_DAYS = 'general/auto_cleaning_days';
    public const AVOID_ANONYMIZATION = 'general/avoid_anonymisation';
    public const ORDER_STATUSES = 'general/order_statuses';
    public const AVOID_GIFT_REGISTRY_ANONYMIZATION = 'general/gift_registry_anonymisation';
    public const EXCLUDED_FIELDS_FOR_DOWNLOAD = 'general/excluded_fields';

    public const EMAIL_ADMIN_NOTIFICATION = 'deletion_notification/enable_admin_notification';
    public const EMAIL_ADMIN_NOTIFICATION_TEMPLATE = 'deletion_notification/admin_template';
    public const EMAIL_ADMIN_NOTIFICATION_SENDER = 'deletion_notification/admin_sender';
    public const EMAIL_ADMIN_NOTIFICATION_RECIEVER = 'deletion_notification/admin_reciever';

    public const EMAIL_ANONYMIZATION_NOTIFICATION_ENABLE =
        'anonymisation_notification/enable_anonymization_notification';
    public const EMAIL_ANONYMIZATION_NOTIFICATION_TEMPLATE = 'anonymisation_notification/template';
    public const EMAIL_ANONYMIZATION_NOTIFICATION_SENDER = 'anonymisation_notification/sender';
    public const EMAIL_ANONYMIZATION_NOTIFICATION_REPLY_TO = 'anonymisation_notification/reply_to';

    public const EMAIL_APPROVE_DELETION_NOTIFICATION_ENABLE =
        'deletion_notification/enable_approve_deletion_notification';
    public const EMAIL_APPROVE_DELETION_NOTIFICATION_TEMPLATE = 'deletion_notification/template';
    public const EMAIL_APPROVE_DELETION_NOTIFICATION_SENDER = 'deletion_notification/sender';
    public const EMAIL_APPROVE_DELETION_NOTIFICATION_REPLY_TO = 'deletion_notification/reply_to';

    public const EMAIL_DENY_DELETION_NOTIFICATION_ENABLE = 'deletion_notification/enable_deny_deletion_notification';
    public const EMAIL_DENY_DELETION_NOTIFICATION_TEMPLATE = 'deletion_notification/deny_template';
    public const EMAIL_DENY_DELETION_NOTIFICATION_SENDER = 'deletion_notification/deny_sender';
    public const EMAIL_DENY_DELETION_NOTIFICATION_REPLY_TO = 'deletion_notification/deny_reply_to';

    public const EMAIL_POLICY_CHANGE_NOTIFICATION_ENABLE =
        'policy_change_notification/enable_policy_change_notification';
    public const EMAIL_POLICY_CHANGE_NOTIFICATION_TEMPLATE = 'policy_change_notification/template';
    public const EMAIL_POLICY_CHANGE_NOTIFICATION_SENDER = 'policy_change_notification/sender';
    public const EMAIL_POLICY_CHANGE_NOTIFICATION_REPLY_TO = 'policy_change_notification/reply_to';

    public const ALLOWED = 'customer_access_control/';
    public const PRIVACY_SETTINGS_TAB_NAME = 'privacy_settings_tab_name';
    public const POLICIES_TEXT_SECTION = 'policies_text_section';
    public const DOWNLOAD = 'download';
    public const DOWNLOAD_MERGE_INTO_ONE_FILE = 'merge_into_one_file';
    public const ANONYMIZE = 'anonymize';
    public const DELETE = 'delete';
    public const GIVEN_CONSENTS = 'given_consents';
    public const CONSENT_OPTING = 'consent_opting';
    public const SKIP_EMPTY_FIELDS = 'customer_access_control/skip_empty_fields';
    public const DISPLAY_DPO_INFO = 'customer_access_control/display_dpo_info';
    public const DPO_SECTION_NAME = 'customer_access_control/dpo_section_name';
    public const DPO_EMAIL = 'customer_access_control/dpo_email';
    public const DPO_INFO = 'customer_access_control/dpo_info';

    public const PERSONAL_DATA_DELETION = 'personal_data/automatic_personal_data_deletion/personal_data_deletion';
    public const PERSONAL_DATA_DELETION_DAYS =
        'personal_data/automatic_personal_data_deletion/personal_data_deletion_days';
    public const PERSONAL_DATA_STORED = 'personal_data/anonymization_data/personal_data_stored';
    public const PERSONAL_DATA_STORED_DAYS = 'personal_data/anonymization_data/personal_data_stored_days';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getPrivacySettingsTabName($scopeCode = null): string
    {
        return (string)$this->getValue(self::ALLOWED . self::PRIVACY_SETTINGS_TAB_NAME, $scopeCode);
    }

    /**
     * @return array
     */
    public function getEEACountryCodes(): array
    {
        return explode(',', (string)$this->getValue(self::PRIVACY_CHECKBOX_EEA_COUNTRIES));
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $path
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getValue($path, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::PATH_PREFIX . '/' . $path, $scopeType, $scopeCode);
    }

    /**
     * @param string $path
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return bool
     */
    public function isSetFlag($path, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH_PREFIX . '/' . $path, $scopeType, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isAdminDeleteNotificationEnabled($scopeCode = null): bool
    {
        return $this->isSetFlag(self::EMAIL_ADMIN_NOTIFICATION, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getAdminNotificationTemplate($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_ADMIN_NOTIFICATION_TEMPLATE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getAdminNotificationSender($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_ADMIN_NOTIFICATION_SENDER, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return array
     */
    public function getAdminNotificationReciever($scopeCode = null): array
    {
        return array_filter(
            preg_split('/\n|\r\n?/', $this->getValue(self::EMAIL_ADMIN_NOTIFICATION_RECIEVER, $scopeCode))
        );
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isLogGuest($scopeCode = null): bool
    {
        return $this->isSetFlag(self::LOG_GUEST, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isAutoCleaning(): bool
    {
        return $this->isSetFlag(self::AUTO_CLEANING);
    }

    /**
     * @return int
     */
    public function getAutoCleaningDays(): int
    {
        return (int)$this->getValue(self::AUTO_CLEANING_DAYS);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isAvoidAnonymization($scopeCode = null): bool
    {
        return $this->isSetFlag(self::AVOID_ANONYMIZATION, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return array
     */
    public function getOrderStatuses($scopeCode = null): array
    {
        return explode(
            ',',
            (string)$this->getValue(self::ORDER_STATUSES, $scopeCode, ScopeInterface::SCOPE_WEBSITE)
        );
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isAvoidGiftRegistryAnonymization($scopeCode = null): bool
    {
        return $this->isSetFlag(
            self::AVOID_GIFT_REGISTRY_ANONYMIZATION,
            $scopeCode,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isAnonymizationNotificationEnabled($scopeCode = null): bool
    {
        return $this->isSetFlag(self::EMAIL_ANONYMIZATION_NOTIFICATION_ENABLE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getAnonymizationEmailTemplate($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_ANONYMIZATION_NOTIFICATION_TEMPLATE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getAnonymizationEmailSender($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_ANONYMIZATION_NOTIFICATION_SENDER, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getAnonymizationEmailReplyTo($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_ANONYMIZATION_NOTIFICATION_REPLY_TO, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isApproveDeletionNotificationEnabled($scopeCode = null): bool
    {
        return $this->isSetFlag(self::EMAIL_APPROVE_DELETION_NOTIFICATION_ENABLE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getApproveDeletionEmailTemplate($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_APPROVE_DELETION_NOTIFICATION_TEMPLATE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getApproveDeletionEmailSender($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_APPROVE_DELETION_NOTIFICATION_SENDER, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getApproveDeletionEmailReplyTo($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_APPROVE_DELETION_NOTIFICATION_REPLY_TO, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isDenyDeletionNotificationEnabled($scopeCode = null): bool
    {
        return $this->isSetFlag(self::EMAIL_DENY_DELETION_NOTIFICATION_ENABLE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getDenyDeletionEmailTemplate($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_DENY_DELETION_NOTIFICATION_TEMPLATE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getDenyDeletionEmailSender($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_DENY_DELETION_NOTIFICATION_SENDER, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getDenyDeletionEmailReplyTo($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_DENY_DELETION_NOTIFICATION_REPLY_TO, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isPolicyChangeNotificationEnabled($scopeCode = null): bool
    {
        return $this->isSetFlag(self::EMAIL_POLICY_CHANGE_NOTIFICATION_ENABLE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getPolicyChangeEmailTemplate($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_POLICY_CHANGE_NOTIFICATION_TEMPLATE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getPolicyChangeEmailSender($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_POLICY_CHANGE_NOTIFICATION_SENDER, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getPolicyChangeEmailReplyTo($scopeCode = null): string
    {
        return (string)$this->getValue(self::EMAIL_POLICY_CHANGE_NOTIFICATION_REPLY_TO, $scopeCode);
    }

    /**
     * @param string      $configPath
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isAllowed($configPath, $scopeCode = null): bool
    {
        return $this->isSetFlag(self::ALLOWED . $configPath, $scopeCode);
    }

    public function isSkipEmptyFields($scopeCode = null): bool
    {
        return $this->isSetFlag(self::SKIP_EMPTY_FIELDS, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isDisplayDpoInfo($scopeCode = null): bool
    {
        return $this->isSetFlag(self::DISPLAY_DPO_INFO, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getDpoSectionName($scopeCode = null): string
    {
        return (string)$this->getValue(self::DPO_SECTION_NAME, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getDpoEmail($scopeCode = null): string
    {
        return (string)$this->getValue(self::DPO_EMAIL, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getDpoInfo($scopeCode = null): string
    {
        return (string)$this->getValue(self::DPO_INFO, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isModuleEnabled($scopeCode = null): bool
    {
        return $this->isSetFlag(self::MODULE_ENABLED, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isDisplayPpPopup($scopeCode = null): bool
    {
        return $this->isSetFlag(self::DISPLAY_PP_POPUP, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string|null
     */
    public function getCountryRestrictions(?string $scopeCode = null): ?string
    {
        return $this->getValue(self::COUNTRY_RESTRICTIONS, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return array
     */
    public function getCountriesCodes(?string $scopeCode = null): array
    {
        $countriesCodes = (string)$this->getValue(self::COUNTRIES, $scopeCode);

        return array_filter(explode(',', $countriesCodes));
    }

    /**
     * @return array
     */
    public function getEuCountriesCodes(): array
    {
        return explode(',', (string)$this->scopeConfig->getValue(self::EU_COUNTRIES));
    }

    /**
     * @return bool
     */
    public function isPersonalDataDeletion(): bool
    {
        return $this->isSetFlag(self::PERSONAL_DATA_DELETION);
    }

    /**
     * @return int
     */
    public function getPersonalDataDeletionDays(): int
    {
        return (int)$this->getValue(self::PERSONAL_DATA_DELETION_DAYS);
    }

    /**
     * @return bool
     */
    public function isPersonalDataStored(): bool
    {
        return $this->isSetFlag(self::PERSONAL_DATA_STORED);
    }

    /**
     * @return int
     */
    public function getPersonalDataStoredDays(): int
    {
        return (int)$this->getValue(self::PERSONAL_DATA_STORED_DAYS);
    }

    /**
     * @return bool
     */
    public function isAnySectionVisible(): bool
    {
        return $this->isAllowed(self::POLICIES_TEXT_SECTION)
            || $this->isAllowed(self::DOWNLOAD)
            || $this->isAllowed(self::ANONYMIZE)
            || $this->isAllowed(self::DELETE);
    }

    /**
     * @return array
     */
    public function getExcludedFields(): array
    {
        $excludedFields = $this->scopeConfig->getValue(self::PATH_PREFIX . '/'
            . self::EXCLUDED_FIELDS_FOR_DOWNLOAD) ?? '';

        return !empty(trim($excludedFields)) ? $this->convertStringToArray($excludedFields) : [];
    }

    public function convertStringToArray(string $data, string $separator = PHP_EOL): array
    {
        return array_filter(array_map('trim', explode($separator, $data)));
    }
}
