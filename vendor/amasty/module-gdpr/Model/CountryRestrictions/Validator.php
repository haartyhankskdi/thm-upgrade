<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\CountryRestrictions;

use Amasty\Base\Model\GetCustomerIp;
use Amasty\Gdpr\Model\Config;
use Amasty\GdprCookie\Model\Config\Source\CountryRestrictions;
use Amasty\Geoip\Model\Geolocation;

class Validator
{
    /**
     * @var GetCustomerIp
     */
    private $getCustomerIp;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var string[]
     */
    private $countries = [];

    public function __construct(
        GetCustomerIp $getCustomerIp,
        Geolocation $geolocation,
        Config $configProvider
    ) {
        $this->getCustomerIp = $getCustomerIp;
        $this->geolocation = $geolocation;
        $this->configProvider = $configProvider;
    }

    public function isCountryAllowed(?string $store = null, ?string $customerIp = null): bool
    {
        $country = $this->getCurrentCustomerCountry($customerIp);
        switch ($this->configProvider->getCountryRestrictions($store)) {
            case CountryRestrictions::ALL_COUNTRIES:
                return true;
            case CountryRestrictions::EEA_COUNTRIES:
                return in_array(
                    $country,
                    $this->configProvider->getEuCountriesCodes()
                );
            case CountryRestrictions::SPECIFIED_COUNTRIES:
                return in_array(
                    $country,
                    $this->configProvider->getCountriesCodes($store)
                );
        }

        return false;
    }

    private function getCurrentCustomerCountry(?string $ip): string
    {
        if (null === $ip) {
            $ip = (string)$this->getCustomerIp->getCurrentIp();
        }

        if (!isset($this->countries[$ip])) {
            $location = $this->geolocation->locate($ip);
            $this->countries[$ip] = $location->getCountry();
        }

        return (string)$this->countries[$ip];
    }
}
