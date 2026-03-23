<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Consent\DataProvider;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Consent\Consent;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\ResourceModel\Collection;
use Amasty\Gdpr\Model\Consent\ResourceModel\CollectionFactory;
use Amasty\Gdpr\Model\ConsentLogger;
use Amasty\Gdpr\Model\Source\CountriesRestrictment;
use Amasty\Gdpr\Model\Visitor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractDataProvider
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Visitor
     */
    protected $visitor;

    public function __construct(
        Config $config,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        Visitor $visitor
    ) {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->visitor = $visitor;
    }

    /**
     * @param string $location
     *
     * @return array
     */
    abstract public function getData(string $location);

    /**
     * @param string $location
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConsentCollection($location)
    {
        $collection = $this->getCollection($location);

        return array_filter($collection->getItems(), function ($consent) {
            return $this->isNeedShowConsent($consent);
        });
    }

    /**
     * For the correct display of checkboxes by country,
     * the checkout must itself filter them out in js.
     * Amasty OneStepCheckout have a lot of $location (payment_method, shipping_method, etc.)
     *
     * @param string $location
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCheckoutConsentCollection($location)
    {
        $collection = $this->getCollection($location);

        return array_filter($collection->getItems(), function ($consent) {
            return $this->isNeedShowConsentByAgreement($consent);
        });
    }

    /**
     * @param Consent $consent
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isNeedShowConsent(Consent $consent)
    {
        return $this->isNeedShowConsentByCountry($consent) && $this->isNeedShowConsentByAgreement($consent);
    }

    /**
     * @param Consent $consent
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isNeedShowConsentByCountry(Consent $consent)
    {
        switch ($consent->getVisibility()) {
            case CountriesRestrictment::ALL_COUNTRIES:
                return true;
            case CountriesRestrictment::EEA_COUNTRIES:
                $countries = $this->config->getEuCountriesCodes();
                break;
            case CountriesRestrictment::SPECIFIED_COUNTRIES:
                $countries = $consent->getCountries() ?: [];
                break;
            default:
                return false;
        }

        $country = $this->visitor->getCountryCode();

        return in_array($country, $countries);
    }

    /**
     * @param Consent $consent
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isNeedShowConsentByAgreement(Consent $consent)
    {
        if ($consent->isHideTheConsentAfterUserLeftTheConsent()) {
            $agreedConsents = $this->visitor->getAgreedConsents();

            return !in_array($consent->getConsentCode(), $agreedConsents);
        } else {
            return true;
        }
    }

    public function haveAgreement(Consent $consent): bool
    {
        $agreedConsents = $this->visitor->getAgreedConsents();

        return in_array($consent->getConsentCode(), $agreedConsents);
    }

    /**
     * @param string $location
     * @return Collection
     * @throws NoSuchEntityException
     */
    private function getCollection(string $location): Collection
    {
        /** @var Collection $collection * */
        $collection = $this->collectionFactory->create();
        $storeId = (int)$this->storeManager->getStore()->getId();
        $collection
            ->addStoreData($storeId)
            ->addMultiValueFilter(ConsentStore::CONSENT_LOCATION, $location)
            ->addFieldToFilter(ConsentStore::IS_ENABLED, true)
            ->addOrder(ConsentStore::SORT_ORDER, Collection::SORT_ORDER_ASC);

        return $collection;
    }
}
