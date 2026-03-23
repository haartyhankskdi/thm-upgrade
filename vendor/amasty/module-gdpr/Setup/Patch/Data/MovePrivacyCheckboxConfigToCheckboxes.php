<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Setup\Patch\Data;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Amasty\Base\Model\Serializer;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Consent;
use Amasty\Gdpr\Model\ConsentLogger;
use Amasty\Gdpr\Model\Consent\Consent as ConsentModel;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\Repository;
use Amasty\Gdpr\Model\Source\CheckboxLocation;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Amasty\Gdpr\Model\Source\CountriesRestrictment;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class MovePrivacyCheckboxConfigToCheckboxes implements DataPatchInterface
{
    private const EEE_COUNTRIES = 'AT,BE,BG,HR,CY,CZ,DK,EE,FI,FR,DE,GR,HU,IS,IE,IT,LV,LI,LT,LU,MT,NL,' .
    'NO,PL,PT,RO,SK,SI,ES,SE,GB,CH';

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var Repository
     */
    private $consentRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $privacyCheckboxConfig = [];

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var CheckboxLocation
     */
    private $locationsProvider;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    public function __construct(
        WriterInterface $configWriter,
        Repository $checkboxRepository,
        StoreManagerInterface $storeManager,
        Serializer $serializer,
        CheckboxLocation $locationsProvider,
        Config $configProvider,
        ConfigInterface $resourceConfig,
        ResourceInterface $moduleResource
    ) {
        $this->configProvider = $configProvider;
        $this->configWriter = $configWriter;
        $this->consentRepository = $checkboxRepository;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->locationsProvider = $locationsProvider;
        $this->resourceConfig = $resourceConfig;
        $this->moduleResource = $moduleResource;
    }

    public function apply(): MovePrivacyCheckboxConfigToCheckboxes
    {
        $setupDataVersion = $this->moduleResource->getDataVersion('Amasty_Gdpr');

        // Check if module was already installed or not.
        // If setup_version present in DB then we don't need to install fixtures, because setup_version is a marker.
        if ($setupDataVersion && version_compare($setupDataVersion, '2.0.0', '<')) {
            //move privacy checkbox from store config to checkboxes, for version < 2.0.0
            $this->loadConfig();
            $this->addEntity(Store::DEFAULT_STORE_ID);

            foreach ($this->storeManager->getStores() as $store) {
                $storeId = $store->getId();

                if (!isset($this->privacyCheckboxConfig[$storeId])) {
                    continue;
                }

                $this->addEntity($storeId);
            }
        }

        return $this;
    }

    private function loadConfig()
    {
        $connection = $this->resourceConfig->getConnection();
        $select = $connection->select();
        $pathPrefix = Config::PATH_PREFIX;
        $select->from($this->resourceConfig->getTable('core_config_data'));
        $select->where("path like '{$pathPrefix}/privacy_checkbox/%'");
        $select->where('scope_id != ?', Store::DEFAULT_STORE_ID);
        $select->where('scope in (?)', [ScopeInterface::SCOPE_STORE, ScopeInterface::SCOPE_STORES]);
        $select->reset(Select::COLUMNS);
        $select->columns(['scope_id', 'path', 'value']);
        $privacyCheckboxConfig = $connection->fetchAll($select);

        if (!empty($privacyCheckboxConfig)) {
            foreach ($privacyCheckboxConfig as $config) {
                $this->privacyCheckboxConfig[$config['scope_id']][$config['path']] = $config['value'];
            }
        }
    }

    /**
     * @param int $store
     *
     * @throws CouldNotSaveException
     */
    private function addEntity($store = Store::DEFAULT_STORE_ID)
    {
        $consent = $this->createEntity($store);
        $consent->setConsentName(__('Privacy Checkbox')->getText());
        $consent->setConsentCode('privacy_checkbox');
        $consent->setConsentLocation($this->getPrivacyConsentLocation($store));
        $this->consentRepository->save($consent);
    }

    private function getPrivacyConsentLocation($store = Store::DEFAULT_STORE_ID): array
    {
        $locations = [
            ConsentLogger::FROM_REGISTRATION,
            ConsentLogger::FROM_CHECKOUT
        ];
        $consentLocations = [];

        foreach ($locations as $location) {
            if ($locationFlag = $this->getConfig("display_at_{$location}", $store)) {
                $consentLocations[] = $location;
            }
        }

        // fix different names in system.xml and other places
        if ($locationFlag = $this->getConfig("display_at_contact", $store)) {
            $consentLocations[] = ConsentLogger::FROM_CONTACTUS;
        }
        // fix different names in system.xml and other places
        if ($locationFlag = $this->getConfig("display_at_newsletter", $store)) {
            $consentLocations[] = ConsentLogger::FROM_SUBSCRIPTION;
        }

        return $consentLocations;
    }

    private function getPrivacyCheckboxText($store = Store::DEFAULT_STORE_ID): string
    {
        $text = $this->getConfig('consent_text', $store);
        $placeholder = Consent\RegistryConstants::LINK_PLACEHOLDER;
        $text = str_replace('a href="#"', 'a href="'. $placeholder .'"', $text);

        return $text;
    }

    /**
     * @param $path
     * @param int $storeId
     *
     * @return bool|string|null
     */
    private function getConfig($path, $storeId = Store::DEFAULT_STORE_ID)
    {
        if ($storeId === Store::DEFAULT_STORE_ID) {
            return $this->configProvider->getValue("privacy_checkbox/{$path}");
        }

        $pathPrefix = Config::PATH_PREFIX;

        return $this->privacyCheckboxConfig[$storeId]["{$pathPrefix}/privacy_checkbox/{$path}"]
            ?? $this->configProvider->getValue("privacy_checkbox/{$path}");
    }

    /**
     * @param int $store
     *
     * @return array|null
     */
    private function getPrivacyCheckboxCountries($store = Store::DEFAULT_STORE_ID)
    {
        $eeaCountriesConfig = $this->getConfig('eea_countries', $store);
        $defaultConfigCountries = array_filter(explode(',', self::EEE_COUNTRIES));

        if ($eeaCountriesConfig === null) {
            return null;
        }

        $configuredCountries = explode(',', $eeaCountriesConfig);

        if (!array_diff($configuredCountries, $defaultConfigCountries)) {
            return null;
        }

        return $configuredCountries;
    }

    /**
     * @param $store
     *
     * @return ConsentModel
     */
    private function createEntity($store)
    {
        /** @var ConsentModel $consent * */
        $consent = $this->consentRepository->getEmptyConsentModel();
        $consentStore = $this->consentRepository->getEmptyConsentStoreModel();
        $consentStore->addData(
            [
                ConsentStore::CONSENT_STORE_ID => $store,
                ConsentStore::IS_ENABLED => true,
                ConsentStore::IS_REQUIRED => true,
                ConsentStore::LOG_THE_CONSENT => true,
                ConsentStore::SORT_ORDER => 0,
                ConsentStore::HIDE_CONSENT_AFTER_USER_LEFT_THE_CONSENT => true,
                ConsentStore::CONSENT_TEXT => $this->getPrivacyCheckboxText($store),
                ConsentStore::LINK_TYPE => ConsentLinkType::PRIVACY_POLICY
            ]
        );
        $consent->setStoreModel($consentStore);

        if (!$this->getConfig('eea_only', $store)) {
            $consent->setVisibility(CountriesRestrictment::ALL_COUNTRIES);
        } else {
            $configCountries = $this->getPrivacyCheckboxCountries($store);
            $visibilityConfig = $configCountries ? CountriesRestrictment::SPECIFIED_COUNTRIES
                : CountriesRestrictment::EEA_COUNTRIES;
            $consent->setVisibility($visibilityConfig);
            $consent->setCountries($configCountries);
        }

        return $consent;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
