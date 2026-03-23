<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2022-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\MollieThemeBundle\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mollie\Payment\Model\MollieConfigProvider;

class EnsureSectionDataGenerationWithoutMollieApiKey
{
    /**
     * @var ScopeConfigInterface
     */
    private $systemConfig;

    public function __construct(ScopeConfigInterface $systemConfig)
    {
        $this->systemConfig = $systemConfig;
    }

    public function aroundGetConfig(MollieConfigProvider $subject, callable $proceed): array
    {
        return $this->systemConfig->getValue('payment/mollie_general/enabled', ScopeInterface::SCOPE_STORES)
            ? $proceed()
            : [];
    }
}
