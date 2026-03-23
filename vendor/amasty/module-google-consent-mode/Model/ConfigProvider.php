<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Consent Mode
 */

namespace Amasty\GoogleConsentMode\Model;

use Amasty\GdprCookie\Model\ConfigProvider as GdprCookieConfigProvider;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends GdprCookieConfigProvider
{
    public const ENABLE_CONSENT_MODE = 'consent_mode/enable';
    public const CONSENT_TYPES = 'consent_mode/consent_types';
    public const DEV_JS_MOVE_SCRIPT_TO_BOTTOM = 'dev/js/move_script_to_bottom';
    public const AMOPTIMIZER_JAVASCRIPT_MOVEJS = 'amoptimizer/javascript/movejs';

    public function isConsentModeEnabled(?int $storeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->isSetFlag(self::ENABLE_CONSENT_MODE, $storeId, $scope);
    }

    public function getConsentTypes(?int $storeId = null, string $scope = ScopeInterface::SCOPE_STORE): string
    {
        return (string)$this->getValue(self::CONSENT_TYPES, $storeId, $scope);
    }

    public function isMoveScriptEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::DEV_JS_MOVE_SCRIPT_TO_BOTTOM)
            || $this->scopeConfig->isSetFlag(self::AMOPTIMIZER_JAVASCRIPT_MOVEJS);
    }
}
