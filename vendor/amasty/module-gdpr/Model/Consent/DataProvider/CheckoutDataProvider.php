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
use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Model\Consent\ResourceModel\CollectionFactory;
use Amasty\Gdpr\Model\Source\CountriesRestrictment;
use Amasty\Gdpr\Model\Visitor;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class CheckoutDataProvider extends AbstractDataProvider
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ConsentPrivacyLinkResolver
     */
    private $consentPrivacyLinkResolver;

    public function __construct(
        Config $config,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        Visitor $visitor,
        Escaper $escaper,
        ConsentPrivacyLinkResolver $consentPrivacyLinkResolver
    ) {
        $this->consentPrivacyLinkResolver = $consentPrivacyLinkResolver;
        $this->escaper = $escaper;

        parent::__construct(
            $config,
            $collectionFactory,
            $storeManager,
            $visitor
        );
    }

    /**
     * @param string $consentLocation
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(string $consentLocation)
    {
        $result = [];

        if (!$this->config->isModuleEnabled()) {
            return $result;
        }

        $result['consents'] = [];

        /** @var Consent $consent**/
        foreach ($this->getCheckoutConsentCollection($consentLocation) as $consent) {
            $text = str_replace(
                RegistryConstants::LINK_PLACEHOLDER,
                $this->consentPrivacyLinkResolver->getPrivacyLink($consent),
                $consent->getConsentText()
            );
            $result['consents'][] = [
                'checkbox_text' => $this->escaper->escapeHtml($text, ['a']),
                'checkbox_code' => $consent->getConsentCode(),
                'title' => $consent->getConsentName(),
                'county_codes' => $this->getCountryCodes($consent),
                'name' => $consent->getConsentCode(),
                'required' => $consent->isRequired(),
                'consent_id' => $consent->getId()
            ];
        }

        $result['meta'] = [
            'where' => $consentLocation
        ];

        return $result;
    }

    /**
     * @param Consent $consent
     * @return array|null
     */
    private function getCountryCodes(Consent $consent): ?array
    {
        $countries = $consent->getCountries();

        if ((int)$consent->getVisibility() === CountriesRestrictment::EEA_COUNTRIES) {
            $countries = $this->config->getEuCountriesCodes();
        }

        return $countries;
    }
}
