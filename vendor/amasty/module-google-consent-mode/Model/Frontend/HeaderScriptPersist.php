<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Consent Mode
 */

namespace Amasty\GoogleConsentMode\Model\Frontend;

use Amasty\GoogleConsentMode\Model\ConfigProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class HeaderScriptPersist implements ArgumentInterface
{
    public const OPEN_SCRIPT_TAG = '<script>';
    public const CLOSE_SCRIPT_TAG = '</script>';
    public const OPEN_REPLACER_TAG = '<am-consent>';
    public const CLOSE_REPLACER_TAG = '</am-consent>';
    public const SCRIPT_TAG_NAME = 'script';
    public const REPLACER_TAG_NAME = 'am-consent';

    /**
     * @var ConfigProvider $configProvider
     */
    private ConfigProvider $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
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

    /**
     * @deprecated backward compatibility
     * @see self::changeScriptTags()
     */
    public function getOpenTag(): string
    {
        return $this->configProvider->isMoveScriptEnabled()
            ? self::OPEN_REPLACER_TAG
            : self::OPEN_SCRIPT_TAG;
    }

    /**
     * @deprecated backward compatibility
     * @see self::changeScriptTags()
     */
    public function getCloseTag(): string
    {
        return $this->configProvider->isMoveScriptEnabled()
            ? self::CLOSE_REPLACER_TAG
            : self::CLOSE_SCRIPT_TAG;
    }

    /**
     * @deprecated backward compatibility
     * @see self::changeScriptTags()
     */
    public function getTagName(): string
    {
        return $this->configProvider->isMoveScriptEnabled()
            ? self::REPLACER_TAG_NAME
            : self::SCRIPT_TAG_NAME;
    }
}
