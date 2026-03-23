<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Plugin\PixelBing\ViewModel\InitBingViewModel;

use Amasty\MicrosoftConsentMode\Model\ConfigProvider;
use Amasty\MicrosoftConsentMode\Model\Cookie\IsMicrosoftCookieAllowed;

class AddDependencyMicrosoftCookie
{
    public function __construct(
        private readonly IsMicrosoftCookieAllowed $isMicrosoftCookieAllowed,
        private readonly ConfigProvider $configProvider
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsMicrosoftCookie($subject, bool $result): bool
    {
        return $this->isMicrosoftCookieAllowed->execute() ?? $result;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsMicrosoftConsentModeEnabled($subject, bool $result): bool
    {
        return $this->configProvider->isMicrosoftConsentModeEnabled() ?? $result;
    }
}
