<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as MagentoConfig;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Class Config to handle all sagepay integrations configs
 */
class Config
{
    /**
     * SagePay VPS protocol
     */
    public const VPS_PROTOCOL       = '4.00';

    /**
     * SagePaySuite Integration codes
     */
    public const METHOD_FORM = 'sagepaysuiteform';
    public const METHOD_PI = 'sagepaysuitepi';
    public const METHOD_PI_MOTO = 'sagepaysuitepimoto';
    public const METHOD_SERVER = 'sagepaysuiteserver';
    public const METHOD_PAYPAL = 'sagepaysuitepaypal';
    public const METHOD_REPEAT = 'sagepaysuiterepeat';

    /**
     * Actions
     */
    public const ACTION_PAYMENT         = 'PAYMENT';
    public const ACTION_PAYMENT_PI      = 'Payment';
    public const ACTION_DEFER           = 'DEFERRED';
    public const ACTION_DEFER_PI        = 'Deferred';
    public const ACTION_AUTHENTICATE    = 'AUTHENTICATE';
    public const ACTION_VOID            = 'VOID';
    public const ACTION_REFUND          = 'REFUND';
    public const ACTION_RELEASE         = 'RELEASE';
    public const ACTION_REPEAT          = 'REPEAT';
    public const ACTION_REPEAT_PI       = 'Repeat';
    public const ACTION_REPEAT_DEFERRED = 'REPEATDEFERRED';
    public const ACTION_AUTHORISE       = 'AUTHORISE';
    public const ACTION_POST            = 'post';
    public const ACTION_ABORT           = 'ABORT';
    public const ACTION_CANCEL          = 'CANCEL';

    /**
     * SagePay MODES
     */
    public const MODE_TEST = 'test';
    public const MODE_LIVE = 'live';
    public const MODE_DEVELOPMENT = 'development';

    /**
     * Sync Order Attempts
     */
    public const ATTEMPT_NUMBER_ONE = 1;
    public const ATTEMPT_NUMBER_THREE = 3;
    public const ATTEMPT_NUMBER_FIVE = 5;

    /**
     * 3D secure MODES
     */
    public const MODE_3D_DEFAULT = 'UseMSPSetting'; // '0' for old integrations
    public const MODE_3D_FORCE = 'Force'; // '1' for old integrations
    public const MODE_3D_DISABLE = 'Disable'; // '2' for old integrations
    public const MODE_3D_IGNORE = 'ForceIgnoringRules'; // '3' for old integrations

    /**
     * AvsCvc MODES
     */
    public const MODE_AVSCVC_DEFAULT = 'UseMSPSetting'; // '0' for old integrations
    public const MODE_AVSCVC_FORCE = 'Force'; // '1' for old integrations
    public const MODE_AVSCVC_DISABLE = 'Disable'; // '2' for old integrations
    public const MODE_AVSCVC_IGNORE = 'ForceIgnoringRules'; // '3' for old integrations

    /**
     * FORM Send Email MODES
     */
    public const MODE_FORM_SEND_EMAIL_NONE = 0; //  Do not send either customer or vendor emails
    public const MODE_FORM_SEND_EMAIL_BOTH = 1; // Send customer and vendor emails if addresses are provided
    public const MODE_FORM_SEND_EMAIL_ONLY_VENDOR = 2; //  Send vendor email but NOT the customer email

    /**
     * Currency settings
     */
    public const CURRENCY_BASE     = "base_currency";
    public const CURRENCY_STORE    = "store_currency";
    public const CURRENCY_SWITCHER = "switcher_currency";

    /**
     * SagePay URLs
     */
    public const URL_FORM_REDIRECT_LIVE = 'https://live.opayo.eu.elavon.com/gateway/service/vspform-register.vsp';
    public const URL_FORM_REDIRECT_TEST = 'https://sandbox.opayo.eu.elavon.com/gateway/service/vspform-register.vsp';
    public const URL_PI_API_LIVE                = 'https://live.opayo.eu.elavon.com/api/v1/';
    public const URL_PI_API_DEV                 = 'http://sandbox.opayo.eu.elavon.com/api/v1/';
    public const URL_PI_API_TEST                = 'https://sandbox.opayo.eu.elavon.com/api/v1/';
    public const URL_REPORTING_API_TEST         = 'https://sandbox.opayo.eu.elavon.com/access/access.htm';
    public const URL_REPORTING_API_LIVE         = 'https://live.opayo.eu.elavon.com/access/access.htm';
    public const URL_REPORTING_API_DEV          = 'http://sandbox.opayo.eu.elavon.com/access/access.htm';
    public const URL_SHARED_VOID_TEST           = 'https://sandbox.opayo.eu.elavon.com/gateway/service/void.vsp';
    public const URL_SHARED_VOID_LIVE           = 'https://live.opayo.eu.elavon.com/gateway/service/void.vsp';
    public const URL_SHARED_CANCEL_TEST         = 'https://sandbox.opayo.eu.elavon.com/gateway/service/cancel.vsp';
    public const URL_SHARED_CANCEL_LIVE         = 'https://live.opayo.eu.elavon.com/gateway/service/cancel.vsp';
    public const URL_SHARED_REFUND_TEST         = 'https://sandbox.opayo.eu.elavon.com/gateway/service/refund.vsp';
    public const URL_SHARED_REFUND_LIVE         = 'https://live.opayo.eu.elavon.com/gateway/service/refund.vsp';
    public const URL_SHARED_RELEASE_TEST        = 'https://sandbox.opayo.eu.elavon.com/gateway/service/release.vsp';
    public const URL_SHARED_RELEASE_LIVE        = 'https://live.opayo.eu.elavon.com/gateway/service/release.vsp';
    public const URL_SHARED_AUTHORISE_TEST      = 'https://sandbox.opayo.eu.elavon.com/gateway/service/authorise.vsp';
    public const URL_SHARED_AUTHORISE_LIVE      = 'https://live.opayo.eu.elavon.com/gateway/service/authorise.vsp';
    public const URL_SHARED_REPEATDEFERRED_TEST = 'https://sandbox.opayo.eu.elavon.com/gateway/service/repeat.vsp';
    public const URL_SHARED_REPEATDEFERRED_LIVE = 'https://live.opayo.eu.elavon.com/gateway/service/repeat.vsp';
    public const URL_SHARED_REPEAT_TEST         = 'https://sandbox.opayo.eu.elavon.com/gateway/service/repeat.vsp';
    public const URL_SHARED_REPEAT_LIVE         = 'https://live.opayo.eu.elavon.com/gateway/service/repeat.vsp';
    public const URL_SHARED_ABORT_TEST          = 'https://sandbox.opayo.eu.elavon.com/gateway/service/abort.vsp';
    public const URL_SHARED_ABORT_LIVE          = 'https://live.opayo.eu.elavon.com/gateway/service/abort.vsp';
    public const URL_SERVER_POST_TEST = 'https://sandbox.opayo.eu.elavon.com/gateway/service/vspserver-register.vsp';
    public const URL_SERVER_POST_DEV  = 'http://sandbox.opayo.eu.elavon.com/gateway/service/vspserver-register.vsp';
    public const URL_SERVER_POST_LIVE = 'https://live.opayo.eu.elavon.com/gateway/service/vspserver-register.vsp';
    public const URL_DIRECT_POST_TEST = 'https://sandbox.opayo.eu.elavon.com/gateway/service/vspdirect-register.vsp';
    public const URL_DIRECT_POST_LIVE = 'https://live.opayo.eu.elavon.com/gateway/service/vspdirect-register.vsp';
    public const URL_PAYPAL_COMPLETION_TEST     = 'https://sandbox.opayo.eu.elavon.com/gateway/service/complete.vsp';
    public const URL_PAYPAL_COMPLETION_LIVE     = 'https://live.opayo.eu.elavon.com/gateway/service/complete.vsp';
    public const URL_TOKEN_POST_REMOVE_LIVE     = 'https://live.opayo.eu.elavon.com/gateway/service/removetoken.vsp';
    public const URL_TOKEN_POST_REMOVE_TEST     = 'https://sandbox.opayo.eu.elavon.com/gateway/service/removetoken.vsp';
    public const URL_CHECK_LICENSE              = 'https://apiserviceslda.licencing.ebizmarts.com/register/';

    /**
     * SagePay Status Codes
     */
    public const SUCCESS_STATUS         = '0000';
    public const AUTH3D_REQUIRED_STATUS = '2007';
    public const AUTH3D_V2_REQUIRED_STATUS = '2021';

    /**
     * SagePay Third Man Score Statuses
     */
    public const T3STATUS_NORESULT = 'NORESULT';
    public const T3STATUS_OK       = 'OK';
    public const T3STATUS_HOLD     = 'HOLD';
    public const T3STATUS_REJECT   = 'REJECT';

    /**
     * SagePay Response Statuses
     */
    public const OK_STATUS             = 'OK';
    public const PENDING_STATUS        = 'PENDING';
    public const REGISTERED_STATUS     = 'REGISTERED';
    public const DUPLICATED_STATUS     = 'DUPLICATED';
    public const AUTHENTICATED_STATUS  = 'AUTHENTICATED';

    /**
     * SagePay ReD Score Statuses
     */
    public const REDSTATUS_ACCEPT     = 'ACCEPT';
    public const REDSTATUS_DENY       = 'DENY';
    public const REDSTATUS_CHALLENGE  = 'CHALLENGE';
    public const REDSTATUS_NOTCHECKED = 'NOTCHECKED';

    /**
     * Basket Formats
     */
    public const BASKETFORMAT_SAGE50   = 'Sage50';
    public const BASKETFORMAT_XML      = 'xml';
    public const BASKETFORMAT_DISABLED = 'Disabled';

    /**
     * Server payment layout
     */
    public const MODAL = 'modal';
    public const REDIRECT_TO_SAGEPAY = 'redirect_to_sagepay';

    /**
     * Cancel Pending Payment Cron minutes
     */
    public const CANCEL_TIMER_15 = '15 MINUTE';
    public const CANCEL_TIMER_30 = '30 MINUTE';
    public const CANCEL_TIMER_45 = '45 MINUTE';

    /**
     * Current payment method code
     *
     * @var string
     */
    private $methodCode;

    /**
     * Current store id
     *
     * @var int
     */
    private $configurationScopeId;

    /** @var string */
    private $configurationScope;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var TypeListInterface
     */
    private $typeList;
    /**
     * @var MagentoConfig
     */
    private $config;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param TypeListInterface $typeList
     * @param MagentoConfig $config
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TypeListInterface $typeList,
        MagentoConfig $config
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->typeList     = $typeList;
        $this->config       = $config;

        $this->configurationScopeId = null;
        $this->configurationScope   = ScopeInterface::SCOPE_STORE;
    }

    /**
     * @param $methodCode
     * @return $this
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
        return $this;
    }

    /**
     * Payment method instance code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->methodCode;
    }

    /**
     * Returns payment configuration value
     *
     * @param string $key
     * @param null $configurationScopeId
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getValue($key, $configurationScopeId = null)
    {
        $resolvedConfigurationScopeId = $this->resolveConfigurationScopeId($configurationScopeId);

        $path = $this->getSpecificConfigPath($key);

        return $this->scopeConfig->getValue($path, $this->configurationScope, $resolvedConfigurationScopeId);
    }

    public function getGlobalValue($key, $configurationScopeId = null)
    {
        $resolvedConfigurationScopeId = $this->resolveConfigurationScopeId($configurationScopeId);

        $path = $this->getGlobalConfigPath($key);

        return $this->scopeConfig->getValue($path, $this->configurationScope, $resolvedConfigurationScopeId);
    }

    public function getAdvancedValue($key)
    {
        $config_value = $this->scopeConfig->getValue(
            $this->getAdvancedConfigPath($key),
            $this->configurationScope,
            $this->configurationScopeId
        );
        return $config_value;
    }

    /**
     * @return int
     */
    public function getCurrentStoreId()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        return $store->getId();
    }

    /**
     * Store ID setter
     *
     * @param int $configurationScopeId
     * @return void
     */
    public function setConfigurationScopeId($configurationScopeId)
    {
        $this->configurationScopeId = (int)$configurationScopeId;
    }

    /**
     * @param string $configurationScope
     */
    public function setConfigurationScope($configurationScope)
    {
        $this->configurationScope = $configurationScope;
    }

    /**
     * Map any supported payment method into a config path by specified field name
     *
     * @param string $fieldName
     * @return string|null
     */
    private function getSpecificConfigPath($fieldName)
    {
        return "payment/{$this->methodCode}/{$fieldName}";
    }

    private function getGlobalConfigPath($fieldName)
    {
        return "sagepaysuite/global/{$fieldName}";
    }

    private function getAdvancedConfigPath($fieldName)
    {
        return "sagepaysuite/advanced/{$fieldName}";
    }

    /**
     * Check whether method active in configuration and supported for merchant country or not
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isMethodActive()
    {
        return $this->getValue("active");
    }

    /**
     * Check whether method active for backend transactions.
     *
     */
    public function isMethodActiveMoto()
    {
        return $this->getValue("active_moto");
    }

    public function getVPSProtocol()
    {
        return self::VPS_PROTOCOL;
    }

    public function getSagepayPaymentAction()
    {
        return $this->getValue("payment_action");
    }

    public function getPaymentAction()
    {
        $action = $this->getValue("payment_action");

        $magentoAction = null;

        switch ($action) {
            case self::ACTION_PAYMENT:
            case self::ACTION_REPEAT:
                $magentoAction = AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
                break;
            case self::ACTION_DEFER:
            case self::ACTION_AUTHENTICATE:
            case self::ACTION_REPEAT_DEFERRED:
                $magentoAction = AbstractMethod::ACTION_AUTHORIZE;
                break;
            default:
                $magentoAction = AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
                break;
        }

        return $magentoAction;
    }

    public function getVendorname()
    {
        return $this->getGlobalValue("vendorname");
    }

    public function getLicense($license = null)
    {
        if ($license) {
            return $license;
        } else {
            return $this->getGlobalValue("license");
        }
    }

    public function getStoreDomain()
    {
        $resolvedConfigurationScopeId = $this->resolveConfigurationScopeId($this->configurationScopeId);
        return $this->scopeConfig->getValue(
            Store::XML_PATH_SECURE_BASE_URL,
            $this->configurationScope,
            $resolvedConfigurationScopeId
        );
    }

    /**
     * @return null|string
     */
    public function getFormEncryptedPassword()
    {
        return $this->getValue("encrypted_password");
    }

    public function getFormSendEmail()
    {
        return $this->getValue("send_email");
    }

    public function getFormVendorEmail()
    {
        return $this->getValue("vendor_email");
    }

    public function getFormEmailMessage()
    {
        return $this->getValue("email_message");
    }

    public function getMode()
    {
        return $this->getGlobalValue("mode");
    }

    public function isTokenEnabled()
    {
        return $this->getGlobalValue("token");
    }

    public function getReportingApiUser()
    {
        return $this->getGlobalValue("reporting_user");
    }

    public function getReportingApiPassword()
    {
        return $this->getGlobalValue("reporting_password");
    }

    public function getPIPassword()
    {
        return $this->getValue("password");
    }

    public function getPIKey()
    {
        return $this->getValue("key");
    }

    /**
     * return 3D secure rules setting
     * @param bool $forceDisable
     * @return mixed|string
     */
    public function get3Dsecure($forceDisable = false)
    {
        $config_value = $this->scopeConfig->getValue(
            $this->getAdvancedConfigPath("threedsecure"),
            $this->configurationScope,
            $this->configurationScopeId
        );

        if ($forceDisable) {
            $config_value = self::MODE_3D_DISABLE;
        }

        if ($this->methodCode != self::METHOD_PI
            && $this->methodCode != self::METHOD_PI_MOTO
        ) {
            $config_value = $this->getThreeDSecureLegacyIntegrations($config_value);
        }

        return $config_value;
    }

    /**
     * return AVS_CVC rules setting
     * @return string
     */
    public function getAvsCvc()
    {
        $configValue = $this->getAdvancedValue("avscvc");

        if ($this->methodCode != self::METHOD_PI
            && $this->methodCode != self::METHOD_PI_MOTO
        ) {
            $configValue = $this->getAvsCvcLegacyIntegrations($configValue);
        }

        return $configValue;
    }

    public function getSyncAttempts()
    {
        return $this->getGlobalValue('sync_orders_attempt');
    }

    public function getAutoInvoiceFraudPassed()
    {
        return $this->getAdvancedValue("fraud_autoinvoice");
    }

    public function getAutoInvoiceScore()
    {
        return $this->getAdvancedValue("fraud_autoinvoice_score");
    }

    public function getNotifyFraudResult()
    {
        return $this->getAdvancedValue("fraud_notify");
    }

    public function getPaypalBillingAgreement()
    {
        return $this->getValue("billing_agreement");
    }

    public function getAllowedCcTypes()
    {
        return $this->getValue("cctypes");
    }

    public function dropInEnabled()
    {
        return (bool)($this->getValue("use_dropin") == 1);
    }

    public function getAreSpecificCountriesAllowed()
    {
        return $this->getValue("allowspecific");
    }

    public function getSpecificCountries()
    {
        return $this->getValue("specificcountry");
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    public function getQuoteCurrencyCode($quote)
    {
        $storeId = $quote->getStoreId();

        $this->setConfigurationScopeId($storeId);
        $currencyConfig = $this->getCurrencyConfig();

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($storeId);

        switch ($currencyConfig) {
            case self::CURRENCY_STORE:
                $currency = $store->getDefaultCurrencyCode();
                break;
            case self::CURRENCY_SWITCHER:
                $currency = $store->getCurrentCurrencyCode();
                break;
            default:
                $currency = $store->getBaseCurrencyCode();
        }

        return $currency;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return float
     */
    public function getQuoteAmount($quote)
    {
        $this->setConfigurationScopeId($quote->getStoreId());
        $currencyConfig = $this->getCurrencyConfig();

        switch ($currencyConfig) {
            case Config::CURRENCY_STORE:
            case Config::CURRENCY_SWITCHER:
                $amount = $quote->getGrandTotal();
                break;
            default:
                $amount = $quote->getBaseGrandTotal();
        }

        return $amount;
    }

    public function getCurrencyConfig()
    {
        return $this->getGlobalValue("currency");
    }

    public function getBasketFormat()
    {
        return $this->getAdvancedValue("basket_format");
    }

    public function isPaypalForceXml()
    {
        return $this->getValue("force_xml");
    }

    public function isGiftAidEnabled()
    {
        return $this->getAdvancedValue("giftaid");
    }

    public function isServerLowProfileEnabled()
    {
        return $this->getValue("profile");
    }

    /**
     * @param $methodCode
     * @return bool
     */
    public function isSagePaySuiteMethod($methodCode)
    {
        return $methodCode == self::METHOD_PAYPAL ||
            $methodCode == self::METHOD_PI ||
            $methodCode == self::METHOD_PI_MOTO ||
            $methodCode == self::METHOD_FORM ||
            $methodCode == self::METHOD_SERVER ||
            $methodCode == self::METHOD_REPEAT;
    }

    /**
     * @param $configValue
     * @return string
     */
    private function getThreeDSecureLegacyIntegrations($configValue)
    {
        //for old integrations
        switch ($configValue) {
            case self::MODE_3D_FORCE:
                $return = '1';
                break;
            case self::MODE_3D_DISABLE:
                $return = '2';
                break;
            case self::MODE_3D_IGNORE:
                $return = '3';
                break;
            default:
                $return = '0';
                break;
        }

        return $return;
    }

    /**
     * @param $action
     * @return null|string
     */
    public function getServiceUrl($action)
    {
        $mode = $this->getMode();

        $constantName = sprintf("self::URL_SHARED_%s_%s", strtoupper($action), strtoupper($mode));

        return constant($constantName);
    }

    /**
     * @param $configValue
     * @return string
     */
    private function getAvsCvcLegacyIntegrations($configValue)
    {
        switch ($configValue) {
            case self::MODE_AVSCVC_FORCE:
                $return = '1';
                break;
            case self::MODE_AVSCVC_DISABLE:
                $return = '2';
                break;
            case self::MODE_AVSCVC_IGNORE:
                $return = '3';
                break;
            default:
                $return = '0';
                break;
        }

        return $return;
    }

    /**
     * @param $configurationScopeId
     * @return int|null
     */
    private function resolveConfigurationScopeId($configurationScopeId)
    {
        if ($configurationScopeId === null) {
            $configurationScopeId = $this->configurationScopeId;
            if ($configurationScopeId === null) {
                $configurationScopeId = $this->getCurrentStoreId();
            }
        }

        return $configurationScopeId;
    }

    public function getInvoiceConfirmationNotification()
    {
        return $this->getAdvancedValue("invoice_confirmation_notification");
    }

    public function getMaxTokenPerCustomer()
    {
        return $this->getAdvancedValue("max_token");
    }

    public function get3dNewWindow()
    {
        return $this->getValue("threed_new_window");
    }

    public function getDebugMode()
    {
        return $this->getAdvancedValue("debug_mode");
    }

    public function getPreventPersonalDataLogging()
    {
        return $this->getAdvancedValue("prevent_personal_data_logging");
    }

    /**
     * @param $path
     * @param $value
     * @param null $storeId
     * @param null $scope
     */
    public function saveConfigValue($path, $value, $storeId = null, $scope = null)
    {
        if ($scope) {
            $this->config->saveConfig($path, $value, $scope, $storeId);
        } else {
            $this->config->saveConfig($path, $value, ScopeInterface::SCOPE_STORE, $storeId);
        }
        $this->typeList->cleanType('config');
    }

    public function getPaymentLayout()
    {
        return $this->getValue('payment_pages_layout');
    }

    /**
     * @return bool
     */
    public function shouldAllowRepeatTransactions()
    {
        return (bool)$this->getGlobalValue('repeat_transaction');
    }
}
