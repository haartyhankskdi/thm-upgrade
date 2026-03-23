<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Plugin\Directory\Model\ResourceModel;

use Amasty\Gdpr\Model\Anonymization\AbstractType;
use Amasty\Gdpr\Model\Config;
use Magento\Backend\Model\Auth\Session;
use Magento\Directory\Model\ResourceModel\Country;

/**
 * Plugin to allow loading of anonymized customer address
 * because country and region code is changed
 */
class CountryPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Session
     */
    private $authSession;

    public function __construct(
        Config $config,
        Session $authSession
    ) {
        $this->config = $config;
        $this->authSession = $authSession;
    }

    public function aroundLoadByCode(
        Country $subject,
        \Closure $proceed,
        \Magento\Directory\Model\Country $country,
        $code
    ) {
        if ($this->isAnonymizationAllowed()
            && $code === AbstractType::ANONYMIZE_COUNTRY_ID
        ) {
            $country->setName('anonymous');
            $country->setNameDefault('anonymous');

            return $country;
        }

        return $proceed($country, $code);
    }

    /**
     * Ignore config settings and allow admin to anonymize or delete customer data.
     */
    private function isAnonymizationAllowed(): bool
    {
        return ($this->config->isModuleEnabled()
                && ($this->config->isAllowed(Config::ANONYMIZE) || $this->config->isAllowed(Config::DELETE)))
            || $this->authSession->isLoggedIn();
    }
}
