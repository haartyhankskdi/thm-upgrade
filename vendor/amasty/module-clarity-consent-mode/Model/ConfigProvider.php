<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Clarity Consent Mode
 */

namespace Amasty\ClarityConsentMode\Model;

use Amasty\Base\Model\Serializer;
use Amasty\ClarityConsentMode\Block\Adminhtml\System\Config\Form\Field\ConsentTypes;
use Amasty\ClarityConsentMode\Model\Cookie\CookieGroupManager;
use Amasty\GdprCookie\Model\ConfigProvider as GdprCookieConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends GdprCookieConfigProvider
{
    public const ENABLE_CONSENT_MODE = 'clarity_consent_mode/enable';
    public const CONSENT_TYPES = 'clarity_consent_mode/clarity_consent_types';

    /**
     * @var CookieGroupManager $cookieGroupManager
     */
    private CookieGroupManager $cookieGroupManager;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    public function __construct(
        Serializer $serializer,
        CookieGroupManager $cookieGroupManager,
        ScopeConfigInterface $scopeConfig,
        array $customisationTypes = [],
    ) {
        parent::__construct($scopeConfig, $customisationTypes);
        $this->cookieGroupManager = $cookieGroupManager;
        $this->serializer = $serializer;
    }

    public function isClarityConsentModeEnabled(?int $storeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->isSetFlag(self::ENABLE_CONSENT_MODE, $storeId, $scope);
    }

    public function getConsentTypes(?int $storeId = null, string $scope = ScopeInterface::SCOPE_STORE): array
    {
        $consentTypes = (string)$this->getValue(self::CONSENT_TYPES, $storeId, $scope);
        $consentTypesArray = $this->serializer->unserialize($consentTypes);

        foreach ($consentTypesArray as $key => $consentType) {
            if ($consentType['cookie_group'] === ConsentTypes::DEFAULT_CONFIG_CLARITY_GROUP) {
                $consentTypesArray[$key]['cookie_group'] = $this->cookieGroupManager->getClarityGroupId();
            }
        }

        return $consentTypesArray;
    }
}
