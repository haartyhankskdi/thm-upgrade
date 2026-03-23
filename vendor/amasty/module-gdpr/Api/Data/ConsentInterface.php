<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Api\Data;

interface ConsentInterface
{
    /**
     * @return int|null
     */
    public function getConsentId();

    /**
     * @param int $consentId
     */
    public function setConsentId(int $consentId);

    /**
     * @param string $consentName
     *
     * @return void
     */
    public function setConsentName(string $consentName);

    /**
     * @return string|null
     */
    public function getConsentName();

    /**
     * @return string|null
     */
    public function getConsentCode();

    /**
     * @param string $consentCode
     *
     * @return void
     */
    public function setConsentCode(string $consentCode);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     */
    public function setStoreId(int $storeId);

    /**
     * @return int|null
     */
    public function getConsentEntityId();

    /**
     * @param int|null $consentEntityId
     *
     * @return void
     */
    public function setConsentEntityId($consentEntityId);

    /**
     * @return bool|null
     */
    public function isEnabled();

    /**
     * @param bool|null $isEnabled
     */
    public function setIsEnabled($isEnabled);

    /**
     * @return bool|null
     */
    public function isRequired();

    /**
     * @param bool|null $isRequired
     */
    public function setIsRequired($isRequired);

    /**
     * @return bool|null
     */
    public function isLogTheConsent();

    /**
     * @param bool|null $isLogTheConsent
     */
    public function setIsLogTheConsent($isLogTheConsent);

    /**
     * @return bool|null
     */
    public function isHideTheConsentAfterUserLeftTheConsent();

    /**
     * @param bool|null $isHideTheConsentAfterUserLeftTheConsent
     */
    public function setIsHideTheConsentAfterUserLeftTheConsent($isHideTheConsentAfterUserLeftTheConsent);

    /**
     * @return string|null
     */
    public function getConsentText();

    /**
     * @param string|null $consentText
     */
    public function setConsentText($consentText);

    /**
     * @return int|null
     */
    public function getVisibility();

    /**
     * @param int|null $visibility
     */
    public function setVisibility($visibility);

    /**
     * @return array|null
     */
    public function getConsentLocation();

    /**
     * @param array|null $locations
     */
    public function setConsentLocation($locations);

    /**
     * @param array|null $countries
     *
     * @return void
     */
    public function setCountries($countries);

    /**
     * @return array|null
     */
    public function getCountries();

    /**
     * @return int|null
     */
    public function getPrivacyLinkType();

    /**
     * @param int $type
     */
    public function setPrivacyLinkType(int $type);

    /**
     * @return bool|null
     */
    public function isConsentAccepted();

    /**
     * @param bool|null $value
     */
    public function setIsConsentAccepted($value);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     */
    public function setSortOrder(int $sortOrder);
}
