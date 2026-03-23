<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Plugin\Framework\App\Response\Http;

use Amasty\MicrosoftConsentMode\Model\ConfigProvider;
use Amasty\MicrosoftConsentMode\Model\Frontend\HeaderScriptPersist;
use Magento\Framework\App\Response\Http;

class UpdateScriptTag
{
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly HeaderScriptPersist $headerScriptPersist
    ) {
    }

    public function beforeSendResponse(Http $subject): void
    {
        $content = $subject->getContent();

        if (is_string($content)
            && $this->configProvider->isMicrosoftConsentModeEnabled()
            && $this->headerScriptPersist->needToReplace()
            && $this->shouldUpdateScriptTag($content)
        ) {
            $subject->setContent($this->headerScriptPersist->changeReplacerTags($content));
        }
    }

    private function shouldUpdateScriptTag(string $content): bool
    {
        return str_contains($content, HeaderScriptPersist::CLOSE_REPLACER_TAG);
    }
}
