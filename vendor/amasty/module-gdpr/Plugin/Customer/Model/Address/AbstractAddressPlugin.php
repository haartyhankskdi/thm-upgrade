<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Plugin\Customer\Model\Address;

use Amasty\Gdpr\Model\Anonymization\AbstractType;
use Amasty\Gdpr\Model\Config;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Model\Address\AbstractAddress;

/**
 * Plugin for country and region anonymization
 * by default Magento doesn't allow to set random values
 * to the region and country
 */
class AbstractAddressPlugin
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

    /**
     * Ignore validation if address is being anonymized.
     */
    public function beforeValidate(AbstractAddress $subject)
    {
        if ($this->isAnonymizationAllowed()
            && $this->isAddressAnonymized($subject)
        ) {
            $subject->setData('should_ignore_validation', true);
        }
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

    private function isAddressAnonymized(AbstractAddress $subject): bool
    {
        return $subject->getRegionId() === AbstractType::ANONYMIZE_REGION_ID
            && $subject->getCountryId() === AbstractType::ANONYMIZE_COUNTRY_ID;
    }
}
