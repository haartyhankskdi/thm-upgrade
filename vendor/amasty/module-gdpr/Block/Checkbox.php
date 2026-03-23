<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\Consent;
use Amasty\Gdpr\Model\Consent\DataProvider\ConsentPrivacyLinkResolver;
use Amasty\Gdpr\Model\Consent\DataProvider\FrontendData;
use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;

class Checkbox extends Template implements IdentityInterface
{
    /**
     * @var string
     */
    protected $_template = 'checkbox.phtml';

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var FrontendData
     */
    protected $dataProvider;

    /**
     * @var ConsentPrivacyLinkResolver
     */
    private $consentPrivacyLinkResolver;

    /**
     * @var Consent\Consent[]
     */
    private $consentsCache;

    public function __construct(
        Template\Context $context,
        FrontendData $dataProvider,
        ConsentPrivacyLinkResolver $consentPrivacyLinkResolver,
        $scope = ConsentLogger::FROM_REGISTRATION,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->dataProvider = $dataProvider;
        $this->consentPrivacyLinkResolver = $consentPrivacyLinkResolver;
        $this->scope = $scope;
    }

    /**
     * Override in child blocks to make checkbox checked by default
     * @param Consent\Consent $consent
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isChecked(Consent\Consent $consent): bool
    {
        return false;
    }

    public function isRequired(Consent\Consent $consent): bool
    {
        return (bool)$consent->isRequired();
    }

    /**
     * @return Consent\Consent[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConsents(): array
    {
        if ($this->consentsCache === null) {
            $this->consentsCache = $this->dataProvider->getData($this->scope);
        }

        return $this->consentsCache;
    }

    public function getConsentText(Consent\Consent $consent): string
    {
        return str_replace(
            RegistryConstants::LINK_PLACEHOLDER,
            $this->consentPrivacyLinkResolver->getPrivacyLink($consent),
            $consent->getConsentText()
        );
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getConsentFromLocation(): string
    {
        return RegistryConstants::CONSENT_FROM;
    }

    public function getUniqueKey(): string
    {
        return 'amprivacy-checkbox-' . uniqid();
    }

    public function getIdentities()
    {
        $identities = array_map(function ($consent) {
            return $consent->getIdentities();
        }, $this->getConsents());

        return !empty($identities) ? array_merge(...$identities) : [];
    }
}
