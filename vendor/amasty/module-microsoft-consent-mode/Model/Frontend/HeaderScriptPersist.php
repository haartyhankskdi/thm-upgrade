<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Model\Frontend;

use Amasty\MicrosoftConsentMode\Model\ConfigProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class HeaderScriptPersist implements ArgumentInterface
{
    public const OPEN_SCRIPT_TAG = '<script>';
    public const CLOSE_SCRIPT_TAG = '</script>';
    public const OPEN_REPLACER_TAG = '<am-microsoft-consent>';
    public const CLOSE_REPLACER_TAG = '</am-microsoft-consent>';
    public const SCRIPT_TAG_NAME = 'script';
    public const REPLACER_TAG_NAME = 'am-consent';

    public function __construct(
        private readonly ConfigProvider $configProvider
    ) {
    }

    public function needToReplace(): bool
    {
        return $this->configProvider->isMoveScriptEnabled();
    }

    public function changeReplacerTags(string $content): string
    {
        return str_replace(
            ['<' . self::REPLACER_TAG_NAME, '</' . self::REPLACER_TAG_NAME . '>'],
            ['<' . self::SCRIPT_TAG_NAME, '</' . self::SCRIPT_TAG_NAME . '>'],
            $content
        );
    }

    public function changeScriptTags(string $content): string
    {
        return str_replace(
            ['<' . self::SCRIPT_TAG_NAME, '</' . self::SCRIPT_TAG_NAME . '>'],
            ['<' . self::REPLACER_TAG_NAME, '</' . self::REPLACER_TAG_NAME . '>'],
            $content
        );
    }
}
