<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Helper;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Config\ModuleVersion;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends AbstractHelper
{
    public const FRONTEND = "frontend";
    public const ADMIN = 'adminhtml';

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config
     */
    private $sagePaySuiteConfig;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ModuleVersion
     */
    private $moduleVersion;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var State
     */
    private $state;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Data constructor.
     * @param Context $context
     * @param Config $config
     * @param DateTime $dateTime
     * @param ModuleVersion $moduleVersion
     * @param StoreManagerInterface $storeManager
     * @param State $state
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        Config $config,
        DateTime $dateTime,
        ModuleVersion $moduleVersion,
        StoreManagerInterface $storeManager,
        State $state,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->sagePaySuiteConfig = $config;
        $this->dateTime           = $dateTime;
        $this->moduleVersion      = $moduleVersion;
        $this->storeManager       = $storeManager;
        $this->state              = $state;
        $this->encryptor          = $encryptor;
    }

    /**
     * Get default sagepay config instance
     * @return \Ebizmarts\SagePaySuite\Model\Config
     */
    public function getSagePayConfig()
    {
        return $this->sagePaySuiteConfig;
    }

    /**
     * @param string $order_id
     * @param string $action
     * @return string
     */
    public function generateVendorTxCode($order_id = "", $action = Config::ACTION_PAYMENT)
    {
        $prefix = "";

        switch ($action) {
            case Config::ACTION_REFUND:
                $prefix = "R";
                break;
            case Config::ACTION_AUTHORISE:
                $prefix = "A";
                break;
            case Config::ACTION_REPEAT:
            case Config::ACTION_REPEAT_PI:
            case Config::ACTION_REPEAT_DEFERRED:
                $prefix = "RT";
                break;
        }

        $sanitizedOrderId = $this->sanitizeOrderId($order_id);
        $date = $this->dateTime->gmtDate('Y-m-d-His');
        $time = $this->dateTime->gmtTimestamp();

        return substr($prefix . $sanitizedOrderId . "-" . $date . $time, 0, 40);
    }

    /**
     * Verify license
     * @return bool
     */
    // @codingStandardsIgnoreStart
    public function verify($license = null, $scope = null, $scopeId = null)
    {
        if ($this->getAreaCode() === self::ADMIN) {
            if (!$scope) {
                $scope = $this->obtainAdminConfigurationScopeCodeFromRequest();
            }
            if (!$scopeId) {
                $scopeId = $this->obtainAdminConfigurationScopeIdFromRequest();
            }
        }
        return $this->validateLicense($license, $scope, $scopeId) &&
            !$this->needToRegister();
    }
    // @codingStandardsIgnoreEnd

    private function needToRegister()
    {
        $currentVersion = $this->moduleVersion->getModuleVersion('Ebizmarts_SagePaySuite');
        $currentVersion = $this->obtainMajorAndMinorVersionFromVersionNumber($currentVersion);
        $registeredVersion = $this->sagePaySuiteConfig->getGlobalValue('register');
        $registeredVersion =
            $registeredVersion ? $this->obtainMajorAndMinorVersionFromVersionNumber($registeredVersion) : "";
        $registeredVendor = $this->sagePaySuiteConfig->getGlobalValue('registervendor');
        $currentVendor = $this->sagePaySuiteConfig->getGlobalValue('vendorname');
        return ($currentVersion != $registeredVersion) || ($registeredVendor != $currentVendor);
    }

    public function validatePhoneNumber($phoneCode, $phoneNumber)
    {
        // Clean the phone number by removing any non-digit characters
        $cleanedPhoneNumber = preg_replace('/\D/', '', $phoneNumber);

        if ($this->isUKPhoneNumber($phoneCode)) {
            $cleanedPhoneNumber = ltrim($cleanedPhoneNumber, '0');
            $phone = '+' . $phoneCode . $cleanedPhoneNumber;
            $pattern = '/^\+44\d{9,10}$/'; // Regular expression pattern for UK phone numbers
            $isValid = preg_match($pattern, $phone);
        } else {
            $phone = '+' . $phoneCode . $cleanedPhoneNumber;
            $pattern = '/^\+?[1-9]\d{1,14}$/'; // Regular expression pattern for a general international phone number
            $isValid = preg_match($pattern, $phone);
        }

        return (bool) $isValid;
    }

    public function isUKPhoneNumber($phoneCode)
    {
        return $phoneCode == 44;
    }

    /**
     * Validate if the provided email address is valid.
     *
     * @param string $email
     * @return bool
     */
    public function validateEmailAddress($email)
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function validateLicense($license = null, $scope = null, $scopeId = null)
    {
        if ($scopeId != null) {
            $this->sagePaySuiteConfig->setConfigurationScopeId($scopeId);
        }
        if ($scope != null) {
            $this->sagePaySuiteConfig->setConfigurationScope($scope);
        }

        $versionNumberToCheck = $this->obtainMajorAndMinorVersionFromVersionNumber(
            $this->moduleVersion->getModuleVersion('Ebizmarts_SagePaySuite')
        );
        $localSignature = $this->localSignature(
            $this->extractHostFromCurrentConfigScopeStoreCheckoutUrl(),
            $versionNumberToCheck
        );

        return ($localSignature == $this->sagePaySuiteConfig->getLicense($license));
    }

    /**
     * @param string $checkoutHostName
     * @param string X.Y $moduleMajorAndMinorVersionNumber
     * @return string
     */
    private function localSignature($checkoutHostName, $moduleMajorAndMinorVersionNumber)
    {
        $md5    = hash("md5", "Ebizmarts_SagePaySuite2" . $moduleMajorAndMinorVersionNumber . $checkoutHostName);
        $key    = hash("sha1", $md5 . "EbizmartsV2");

        return $key;
    }

    /**
     * @param string semver$versionNumber
     * @return string
     */
    public function obtainMajorAndMinorVersionFromVersionNumber($versionNumber)
    {
        $versionArray = explode('.', $versionNumber);

        return $versionArray[0] . "." . $versionArray[1];
    }

    /**
     * @return int
     */
    public function obtainConfigurationScopeIdFromRequest()
    {
        if ($this->getAreaCode() === self::FRONTEND) {
            return $this->getStoreId();
        }
        return $this->obtainAdminConfigurationScopeIdFromRequest();
    }

    /**
     * @return string
     */
    public function obtainConfigurationScopeCodeFromRequest()
    {
        if ($this->getAreaCode() === self::FRONTEND) {
            return $this->storeScopeCode();
        }
        return $this->obtainAdminConfigurationScopeCodeFromRequest();
    }

    /**
     * @return string
     */
    private function extractHostFromCurrentConfigScopeStoreCheckoutUrl()
    {
        $domain = preg_replace(
            ["/^http:\/\//", "/^https:\/\//", "/^www\./", "/\/$/"],
            "",
            $this->sagePaySuiteConfig->getStoreDomain()
        );

        return $domain;
    }

    /**
     * Stripe transaction if from '-capture/-refund/etc' appends
     * @param $transactionId
     * @return mixed
     */
    public function clearTransactionId($transactionId)
    {
        $suffixes = [
            '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,
            '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID,
            '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND,
        ];
        foreach ($suffixes as $suffix) {
            if ($transactionId && strpos($transactionId, $suffix) !== false) {
                $transactionId = str_replace($suffix, '', $transactionId);
            }
        }
        return $transactionId;
    }

    public function removeCurlyBraces($text)
    {
        return str_replace(["{", "}"], "", $text);
    }

    /**
     * @param string $methodCode
     * @return bool
     */
    public function methodCodeIsSagePay($methodCode)
    {
        return $methodCode == \Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM
            || $methodCode == \Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL
            || $methodCode == \Ebizmarts\SagePaySuite\Model\Config::METHOD_REPEAT
            || $methodCode == \Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER
            || $methodCode == \Ebizmarts\SagePaySuite\Model\Config::METHOD_PI;
    }

    /**
     * @return string
     */
    private function defaultScopeCode()
    {
        return \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
    }

    /**
     * @return string
     */
    private function storeScopeCode()
    {
        return \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    /**
     * @return string
     */
    private function websiteScopeCode()
    {
        return \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
    }

    /**
     * @param $configurationScope
     * @return bool
     */
    public function isConfigurationScopeStore($configurationScope)
    {
        return $configurationScope == $this->storeScopeCode();
    }

    /**
     * @param $configurationScope
     * @return bool
     */
    public function isConfigurationScopeWebsite($configurationScope)
    {
        return $configurationScope == $this->websiteScopeCode();
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function getAreaCode()
    {
        return $this->state->getAreaCode();
    }

    /**
     * @return string
     */
    public function obtainAdminConfigurationScopeCodeFromRequest()
    {
        $configurationScope = $this->defaultScopeCode();

        /** @var $requestObject \Magento\Framework\App\RequestInterface */
        $requestObject = $this->getRequest();

        $storeParameter = $requestObject->getParam('store');
        if ($storeParameter !== null) {
            $configurationScope = $this->storeScopeCode();
        } else {
            $websiteParameter = $requestObject->getParam('website');
            if ($websiteParameter !== null) {
                $configurationScope = $this->websiteScopeCode();
            }
        }

        return $configurationScope;
    }

    /**
     * @return int
     */
    public function obtainAdminConfigurationScopeIdFromRequest()
    {
        $configurationScopeId = $this->getDefaultStoreId();

        /** @var $requestObject \Magento\Framework\App\RequestInterface */
        $requestObject = $this->getRequest();

        $configurationScope = $this->obtainConfigurationScopeCodeFromRequest();
        if ($this->isConfigurationScopeStore($configurationScope)) {
            $configurationScopeId = $requestObject->getParam('store');
        } elseif ($this->isConfigurationScopeWebsite($configurationScope)) {
            $configurationScopeId = $requestObject->getParam('website');
        }

        return $configurationScopeId;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_getRequest();
    }

    /**
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * @param $text
     * @return string
     */
    private function sanitizeOrderId($text)
    {
        return preg_replace("/[^a-zA-Z0-9-\-\{\}\_\.]/", "", $text);
    }

    /**
     * @param array $data
     * @return array
     */
    public function removePersonalInformation($data)
    {
        if ($this->sagePaySuiteConfig->getPreventPersonalDataLogging()) {
            $fieldsNames = $this->getPersonalInfoFieldsNames();
            $data = $this->findAndReplacePersonalInformation($data, $fieldsNames);
            $data = $this->removePiShippingBillingInformation($data);
            $data = $this->removeBasketXml($data);
        }

        return $data;
    }

    /**
     * @param Object $data
     * @return array|Object
     */
    public function removePersonalInformationObject($data)
    {
        $array = $data;
        if ($this->sagePaySuiteConfig->getPreventPersonalDataLogging()) {
            $array = json_decode(json_encode($data), true);
            if (!empty(json_last_error())) {
                return $data;
            }
            $array = $this->removePersonalInformation($array);
        }

        return $array;
    }

    /**
     * @param array $array
     * @param array $fieldsNames
     * @return array
     */
    private function findAndReplacePersonalInformation(array $array, array $fieldsNames)
    {
        foreach ($fieldsNames as $field) {
            if (isset($array[$field]) && !empty($array[$field])) {
                $firstChar = substr($array[$field], 0, 1);
                $lastChar = substr($array[$field], -1);
                $array[$field] = $firstChar . "XXXXXXXXX" . $lastChar;
            }
        }

        return $array;
    }

    /**
     * @return string[]
     */
    private function getPersonalInfoFieldsNames()
    {
        return [
            "CustomerEMail",
            "BillingSurname",
            "BillingFirstnames",
            "BillingAddress1",
            "BillingAddress2",
            "BillingCity",
            "BillingPostCode",
            "BillingPhone",
            "DeliverySurname",
            "DeliveryFirstnames",
            "DeliveryAddress1",
            "DeliveryAddress2",
            "DeliveryCity",
            "DeliveryPostCode",
            "DeliveryPhone",
            "customer_email",
            "customer_firstname",
            "customer_lastname",
            "customer_middlename",
            "customerFirstName",
            "customerLastName",
            "customerEmail",
            "customerPhone",
            "customeremail",
            "billingsurname",
            "billingfirstnames",
            "billingaddress",
            "billingaddress2",
            "billingcity",
            "billingpostcode",
            "billingphone",
            "deliverysurname",
            "deliveryfirstnames",
            "deliveryaddress",
            "deliveryaddress2",
            "deliverycity",
            "deliverypostcode",
            "deliveryphone",
            "cardholder",
            "cardaddress",
            "cardaddress2",
            "cardcity",
            "cardpostcode",
            "BasketXML",
            "cardfirstnames",
            "cardsurname"
        ];
    }

    /**
     * @return string[]
     */
    private function getPiShippingDetailsFields()
    {
        return [
            "recipientFirstName",
            "recipientLastName",
            "shippingAddress1",
            "shippingAddress2",
            "shippingCity",
            "shippingPostalCode"
        ];
    }

    /**
     * @return string[]
     */
    private function getPiBillingAddressFields()
    {
        return [
            "address1",
            "address2",
            "city",
            "postalCode"
        ];
    }

    /**
     * @return string[]
     */
    private function getBasketXmlFieldNames()
    {
        return [
            "recipientEmail",
            "recipientLName",
            "recipientFName",
            "recipientPhone",
            "recipientAdd1",
            "recipientPostCode",
            "recipientCity"

        ];
    }

    /**
     * @param array $data
     * @return array
     */
    private function removePiShippingBillingInformation(array $data): array
    {
        if (isset($data["shippingDetails"]) && !empty($data["shippingDetails"])) {
            $piShippingDetailsFieldsName = $this->getPiShippingDetailsFields();
            $data['shippingDetails'] = $this->findAndReplacePersonalInformation(
                $data['shippingDetails'],
                $piShippingDetailsFieldsName
            );
        }

        if (isset($data["billingAddress"]) && !empty($data["billingAddress"])) {
            $piBillingAddressFieldsName = $this->getPiBillingAddressFields();
            $data['billingAddress'] = $this->findAndReplacePersonalInformation(
                $data['billingAddress'],
                $piBillingAddressFieldsName
            );
        }
        return $data;
    }

    private function removeBasketXml($data)
    {
        if (isset($data['basketxml']['basket']) && !empty($data['basketxml']['basket'])) {
            $basketXmlFieldsName = $this->getBasketXmlFieldNames();
            foreach ($data['basketxml']['basket'] as $key => $value) {
                if ($key === 'item') {
                    if (isset($value['description'])) {
                        $data['basketxml']['basket'][$key] = $this->findAndReplacePersonalInformation(
                            $value,
                            $basketXmlFieldsName
                        );
                    } else {
                        foreach ($value as $keyItem => $item) {
                            $data['basketxml']['basket'][$key][$keyItem] = $this->findAndReplacePersonalInformation(
                                $item,
                                $basketXmlFieldsName
                            );
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote
     * @param \Magento\Checkout\Model\Session $session
     * @param $hash
     */
    public function addValidationHashToQuote($quote)
    {
        if ($this->sagePaySuiteConfig->getAdvancedValue("quote_validator")) {
            $hash = $this->encryptor->encrypt($quote->getId());
            $quote->setSagePayQuoteHash($hash);
        }
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote
     * @return string
     */
    public function isValidQuoteHash($quote)
    {
        $hash = (string)$quote->getSagePayQuoteHash();
        $decryptedValue = $this->encryptor->decrypt($hash);
        return $quote->getId() === $decryptedValue;
    }
}
