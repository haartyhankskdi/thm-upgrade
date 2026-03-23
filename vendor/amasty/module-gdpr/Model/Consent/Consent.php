<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Consent;

use Amasty\Gdpr\Api\Data\ConsentInterface;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStoreFactory;
use Amasty\Gdpr\Model\Consent\ResourceModel\Consent as ConsentResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;

class Consent extends AbstractModel implements ConsentInterface, \JsonSerializable, IdentityInterface
{
    public const CACHE_TAG = 'amgdpr_consent';

    public const ID = 'consent_id';
    public const CONSENT_NAME = 'name';
    public const CONSENT_CODE = 'consent_code';
    public const IS_CONSENT_ACCEPTED = 'consent_accepted';

    /**
     * @var ConsentStore
     */
    private $storeModel;

    /**
     * @var ConsentStoreFactory
     */
    private $consentStoreFactory;

    public function __construct(
        ConsentStoreFactory $consentStoreFactory,
        Context $context,
        Registry $registry,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->consentStoreFactory = $consentStoreFactory;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();

        $this->_init(ConsentResource::class);
        $this->setIdFieldName(self::ID);
    }

    /**
     * @return ConsentStore
     */
    public function getStoreModel()
    {
        if ($this->storeModel === null) {
            $this->storeModel = $this->consentStoreFactory->create();
        }

        return $this->storeModel;
    }

    /**
     * @param ConsentStore $consentStore
     */
    public function setStoreModel(ConsentStore $consentStore)
    {
        $this->storeModel = $consentStore;
    }

    /**
     * @return int|null
     */
    public function getConsentId()
    {
        return $this->_getData(self::ID) === null ? null : (int)$this->_getData(self::ID);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function _getData($key)
    {
        $data = parent::_getData($key);

        if ($data === null) {
            return $this->getStoreModel()->_getData($key);
        }

        return $data;
    }

    /**
     * @param int $consentId
     */
    public function setConsentId(int $consentId)
    {
        $this->setData(self::ID, $consentId);
    }

    /**
     * @param string $consentName
     */
    public function setConsentName(string $consentName)
    {
        $this->setData(self::CONSENT_NAME, $consentName);
    }

    /**
     * @return string|null
     */
    public function getConsentName()
    {
        return $this->_getData(self::CONSENT_NAME) === null ?
            null : (string)$this->_getData(self::CONSENT_NAME);
    }

    /**
     * @return string|null
     */
    public function getConsentCode()
    {
        return $this->_getData(self::CONSENT_CODE) === null ?
            null : (string)$this->_getData(self::CONSENT_CODE);
    }

    /**
     * @param string $consentCode
     */
    public function setConsentCode(string $consentCode)
    {
        $this->setData(self::CONSENT_CODE, $consentCode);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_getData(ConsentStore::CONSENT_STORE_ID) === null ?
            Store::DEFAULT_STORE_ID : (int)$this->_getData(ConsentStore::CONSENT_STORE_ID);
    }

    /**
     * @param int $storeId
     */
    public function setStoreId(int $storeId)
    {
        $this->getStoreModel()->setData(ConsentStore::CONSENT_STORE_ID, $storeId);
    }

    /**
     * @return int|null
     */
    public function getConsentEntityId()
    {
        return $this->_getData(ConsentStore::CONSENT_ENTITY_ID) === null ?
            null : (int)$this->_getData(ConsentStore::CONSENT_ENTITY_ID);
    }

    /**
     * @param int|null $consentEntityId
     */
    public function setConsentEntityId($consentEntityId)
    {
        $this->getStoreModel()->setData(ConsentStore::CONSENT_ENTITY_ID, $consentEntityId);
    }

    /**
     * @return bool|null
     */
    public function isEnabled()
    {
        return $this->_getData(ConsentStore::IS_ENABLED) === null ?
            null : (bool)$this->_getData(ConsentStore::IS_ENABLED);
    }

    /**
     * @param bool|null $isEnabled
     */
    public function setIsEnabled($isEnabled)
    {
        $this->getStoreModel()->setData(ConsentStore::IS_ENABLED, $isEnabled);
    }

    /**
     * @return bool|null
     */
    public function isRequired()
    {
        return $this->_getData(ConsentStore::IS_REQUIRED) === null ?
            null : (bool)$this->_getData(ConsentStore::IS_REQUIRED);
    }

    /**
     * @param bool|null $isRequired
     */
    public function setIsRequired($isRequired)
    {
        $this->getStoreModel()->setData(ConsentStore::IS_REQUIRED, $isRequired);
    }

    /**
     * @return bool|null
     */
    public function isLogTheConsent()
    {
        return $this->_getData(ConsentStore::LOG_THE_CONSENT) === null ?
            null : (bool)$this->_getData(ConsentStore::LOG_THE_CONSENT);
    }

    /**
     * @param bool|null $isLogTheConsent
     */
    public function setIsLogTheConsent($isLogTheConsent)
    {
        $this->getStoreModel()->setData(ConsentStore::LOG_THE_CONSENT, $isLogTheConsent);
    }

    /**
     * @return bool|null
     */
    public function isHideTheConsentAfterUserLeftTheConsent()
    {
        return $this->_getData(ConsentStore::HIDE_CONSENT_AFTER_USER_LEFT_THE_CONSENT) === null ?
            null : (bool)$this->_getData(ConsentStore::HIDE_CONSENT_AFTER_USER_LEFT_THE_CONSENT);
    }

    /**
     * @param bool|null $isHideTheConsentAfterUserLeftTheConsent
     */
    public function setIsHideTheConsentAfterUserLeftTheConsent($isHideTheConsentAfterUserLeftTheConsent)
    {
        $this->getStoreModel()->setData(
            ConsentStore::HIDE_CONSENT_AFTER_USER_LEFT_THE_CONSENT,
            $isHideTheConsentAfterUserLeftTheConsent
        );
    }

    /**
     * @return string|null
     */
    public function getConsentText()
    {
        $text = $this->_getData(ConsentStore::CONSENT_TEXT);

        return $text === null ? null : (string)$text;
    }

    /**
     * @param string|null $consentText
     */
    public function setConsentText($consentText)
    {
        $this->getStoreModel()->setData(ConsentStore::CONSENT_TEXT, $consentText);
    }

    /**
     * @return int|null
     */
    public function getVisibility()
    {
        return $this->_getData(ConsentStore::VISIBILITY) === null ?
            null : (int)$this->_getData(ConsentStore::VISIBILITY);
    }

    /**
     * @param int|null $visibility
     */
    public function setVisibility($visibility)
    {
        $this->getStoreModel()->setData(ConsentStore::VISIBILITY, $visibility);
    }

    /**
     * @return array|null
     */
    public function getConsentLocation()
    {
        $consentLocation = $this->_getData(ConsentStore::CONSENT_LOCATION);
        return $consentLocation === null ?
            null : array_filter(explode(',', $consentLocation));
    }

    /**
     * @param array|null $locations
     */
    public function setConsentLocation($locations)
    {
        $this->getStoreModel()->setData(ConsentStore::CONSENT_LOCATION, implode(',', $locations));
    }

    /**
     * @return array|null
     */
    public function getCountries()
    {
        $countries = $this->_getData(ConsentStore::COUNTRIES);
        return $countries === null ?
            null : array_filter(explode(',', $countries));
    }

    /**
     * @param array|null $countries
     */
    public function setCountries($countries)
    {
        if ($countries !== null) {
            $countries = implode(',', $countries);
        }

        $this->getStoreModel()->setData(ConsentStore::COUNTRIES, $countries);
    }

    /**
     * @return string|null
     */
    public function getPrivacyLinkType()
    {
        return $this->_getData(ConsentStore::LINK_TYPE) === null ?
            null : (int)$this->_getData(ConsentStore::LINK_TYPE);
    }

    /**
     * @param int $type
     */
    public function setPrivacyLinkType(int $type)
    {
        $this->getStoreModel()->setData(ConsentStore::LINK_TYPE, $type);
    }

    /**
     * @return bool|null
     */
    public function isConsentAccepted()
    {
        return $this->_getData(Consent::IS_CONSENT_ACCEPTED) === null ?
            null : (bool)$this->_getData(Consent::IS_CONSENT_ACCEPTED);
    }

    /**
     * @param bool|null $value
     */
    public function setIsConsentAccepted($value)
    {
        $this->setData(Consent::IS_CONSENT_ACCEPTED, $value);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            $this->getData(),
            $this->getStoreModel()->getData()
        );
    }

    /**
     * @return int|null
     */
    public function getSortOrder()
    {
        return $this->_getData(ConsentStore::SORT_ORDER) === null ?
            null : (int)$this->_getData(ConsentStore::SORT_ORDER);
    }

    /**
     * @param int $sortOrder
     */
    public function setSortOrder(int $sortOrder)
    {
        $this->getStoreModel()->setData(ConsentStore::SORT_ORDER, $sortOrder);
    }

    public function getIdentities()
    {
        return [
            self::CACHE_TAG,
            self::CACHE_TAG . '_' . $this->getConsentId()
        ];
    }

    public function getCacheTags()
    {
        $tags = parent::getCacheTags();
        if (!$tags) {
            $tags = [];
        }
        return $tags + $this->getIdentities();
    }
}
