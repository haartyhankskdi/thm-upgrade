<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Hyva Compatibility (System)
 */

namespace Amasty\GdprCookieHyva\ViewModel;

use Amasty\GdprCookie\Block\Consent;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class JsLayout implements ArgumentInterface, IdentityInterface
{
    private const CONFIG_START_PATH = 'config';
    private const POLICY_TEXT = 'policyText';
    private const BUTTONS = 'buttons';
    private const BUTTON_DATA_JS = 'dataJs';
    private const ALLOWED_POLICY_TEXT_TAGS = [
        'a', 'b', 'strong', 'i', 'em', 'mark', 'small', 'del', 'ins', 'sub', 'sup'
    ];
    private const POLICY_ALLOWED = 'isCookiePolicyAllowed';
    private const ALLOW_CUSTOMERS_CLOSE_BAR = 'isAllowCustomersCloseBar';

    /**
     * @var array
     */
    private array $jsLayout;

    /**
     * @var ArrayManager
     */
    private ArrayManager $arrayManager;

    public function __construct(
        Consent      $blockConsent,
        ArrayManager $arrayManager
    ) {
        $this->jsLayout = $blockConsent->getJsLayout();
        $this->arrayManager = $arrayManager;
    }

    public function getLsLayout(): array
    {
        return $this->jsLayout;
    }

    public function getPolicyText(): ?string
    {
        $path = $this->arrayManager->findPath(self::POLICY_TEXT, $this->jsLayout, self::CONFIG_START_PATH);

        return $path ? $this->arrayManager->get($path, $this->jsLayout) : null;
    }

    public function getAllowedPolicyTextTags(): array
    {
        return self::ALLOWED_POLICY_TEXT_TAGS;
    }

    public function getButtonsConfig(): array
    {
        $path = $this->arrayManager->findPath(self::BUTTONS, $this->jsLayout, self::CONFIG_START_PATH);

        return $path ? $this->arrayManager->get($path, $this->jsLayout) : [];
    }

    public function getButtonConfig(string $dataJs): ?array
    {
        $buttonsConfig = $this->getButtonsConfig();
        $buttonIndex = array_search($dataJs, array_column($buttonsConfig, self::BUTTON_DATA_JS), true);

        return $buttonIndex !== false ? $buttonsConfig[$buttonIndex] : null;
    }

    public function isCookiePolicyAllowed(): bool
    {
        $path = $this->arrayManager->findPath(self::POLICY_ALLOWED, $this->jsLayout);

        return (bool)$this->arrayManager->get($path, $this->jsLayout);
    }

    /**
     * @return string[]
     */
    public function getIdentities(): array
    {
        return ['amIsCookiePolicyAllowed-' . $this->isCookiePolicyAllowed()];
    }

    public function isAllowCustomersCloseBar(): bool
    {
        $path = $this->arrayManager->findPath(self::ALLOW_CUSTOMERS_CLOSE_BAR, $this->jsLayout);

        return (bool)$this->arrayManager->get($path, $this->jsLayout);
    }
}
