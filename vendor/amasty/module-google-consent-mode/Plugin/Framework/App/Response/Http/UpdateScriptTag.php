<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Consent Mode
 */

namespace Amasty\GoogleConsentMode\Plugin\Framework\App\Response\Http;

use Amasty\GoogleConsentMode\Model\ConfigProvider;
use Amasty\GoogleConsentMode\Model\Frontend\HeaderScriptPersist;
use Magento\Framework\App\Response\Http;

class UpdateScriptTag
{
    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var HeaderScriptPersist $headerScriptPersist
     */
    private HeaderScriptPersist $headerScriptPersist;

    public function __construct(
        ConfigProvider $configProvider,
        HeaderScriptPersist $headerScriptPersist
    ) {
        $this->configProvider = $configProvider;
        $this->headerScriptPersist = $headerScriptPersist;
    }

    public function beforeSendResponse(Http $subject): void
    {
        $content = $subject->getContent();

        if (is_string($content)
            && $this->configProvider->isConsentModeEnabled()
            && $this->headerScriptPersist->needToReplace()
            && $this->shouldUpdateScriptTag($content)
        ) {
            $subject->setContent($this->headerScriptPersist->changeReplacerTags($content));
        }
    }

    private function shouldUpdateScriptTag(string $content): bool
    {
        return strpos($content, HeaderScriptPersist::CLOSE_REPLACER_TAG) !== false;
    }
}
