<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\WithConsentInterface;
use Amasty\Gdpr\Model\ResourceModel\WithConsent\CollectionFactory;
use Amasty\Geoip\Model\Geolocation;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Store\Model\StoreManagerInterface;

class Visitor
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var CollectionFactory
     */
    private $withConsentCollectionFactory;

    /**
     * @var PolicyRepository
     */
    private $policyRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Stored Agreed Consents by customerId and storeId
     *
     * @var array
     */
    private $agreedConsents = [];

    /**
     * @var string|bool|null
     */
    private $cacheCountryCode = null;

    public function __construct(
        Config $config,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Geolocation $geolocation,
        RemoteAddress $remoteAddress,
        CollectionFactory $withConsentCollectionFactory,
        PolicyRepository $policyRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->geolocation = $geolocation;
        $this->remoteAddress = $remoteAddress;
        $this->withConsentCollectionFactory = $withConsentCollectionFactory;
        $this->policyRepository = $policyRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountryCode()
    {
        if ($this->cacheCountryCode === null) {
            $this->cacheCountryCode = $this->resolveCountryCode();
        }

        return $this->cacheCountryCode;
    }

    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function resolveCountryCode()
    {
        $quote = $this->checkoutSession->getQuote();

        if ($countryCode = $quote->getShippingAddress()->getCountry()) {
            return (string)$countryCode;
        }

        if ($countryCode = $quote->getBillingAddress()->getCountry()) {
            return (string)$countryCode;
        }

        $customer = $this->customerSession->getCustomer();

        if ($customer && ($address = $customer->getPrimaryBillingAddress())) {
            if ($countryCode = $address->getCountry()) {
                return (string)$countryCode;
            }
        }

        if ($countryCode = $this->locate()) {
            return (string)$countryCode;
        }

        return false;
    }

    protected function locate()
    {
        if ($this->customerSession->hasData('amgdpr_country')) {
            return $this->customerSession->getData('amgdpr_country');
        }

        $geolocationResult = $this->geolocation->locate($this->getRemoteIp());

        $result = isset($geolocationResult['country']) ? $geolocationResult['country'] : false;

        $this->customerSession->setData('amgdpr_country', $result);

        return $result;
    }

    public function getRemoteIp()
    {
        $ip = $this->remoteAddress->getRemoteAddress();
        $ip = substr($ip, 0, strrpos($ip, ".")) . '.0';

        return $ip;
    }

    protected function isEEACountry($countryCode)
    {
        return in_array($countryCode, $this->config->getEEACountryCodes());
    }

    /**
     * Getting already agreed consents
     *
     * @return array
     */
    public function getAgreedConsents()
    {
        if ($this->isSessionInvalid()) {
            $this->customerSession->start();
        }
        $customerId = $this->customerSession->getCustomerId();
        $storeId = $this->storeManager->getStore()->getId();
        $cacheKey = sprintf('%d-%d', $customerId, $storeId);

        if (isset($this->agreedConsents[$cacheKey])) {
            return $this->agreedConsents[$cacheKey];
        }

        $currentPolicy = $this->policyRepository->getCurrentPolicy($storeId);

        if ($customerId && $storeId) {
            $consentLogCollection = $this->withConsentCollectionFactory->create();
            $consentLogCollection->filterByLastConsentRecord()
                ->filterByPolicyVersionAndLinkType($currentPolicy->getPolicyVersion(), $storeId)
                ->addFieldToFilter('main_table.' . WithConsentInterface::CUSTOMER_ID, $customerId)
                ->addFieldToFilter('main_table.' . WithConsentInterface::ACTION, true);

            $this->agreedConsents[$cacheKey] = $consentLogCollection->getColumnValues(
                WithConsentInterface::CONSENT_CODE
            );
        } else {
            $this->agreedConsents[$cacheKey] = [];
        }

        return $this->agreedConsents[$cacheKey];
    }

    private function isSessionInvalid(): bool
    {
        return !$this->customerSession->isSessionExists()
            || ($this->customerSession->getCustomerGroupId() != GroupInterface::NOT_LOGGED_IN_ID
                && $this->customerSession->getCustomerId() === null);
    }

    public function _resetState(): void
    {
        $this->cacheCountryCode = null;
    }
}
