<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\ViewModel;

use Amasty\Base\Model\Serializer;
use Amasty\MicrosoftConsentMode\Model\ConfigProvider;
use Amasty\MicrosoftConsentMode\Model\Frontend\HeaderScriptPersist;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManager;

class MicrosoftViewModel implements ArgumentInterface
{
    public const COOKIE_MODULE = 'Amasty_GdprCookie';

    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly HeaderScriptPersist $headerScriptPersist,
        private readonly Serializer $serializer,
        private readonly StoreManager $storeManager
    ) {
    }

    public function getConsentTypesData(): string
    {
        $result = [];
        $types = $this->serializer->unserialize($this->configProvider->getConsentTypes());
        foreach ($types as $type) {
            $result[$type['consent_type']] = ['default' => $type['default_status'], 'group' => $type['cookie_group']];
        }

        return $this->serializer->serialize($result);
    }

    public function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    public function replaceTags(string $content): string
    {
        if ($this->headerScriptPersist->needToReplace()) {
            $content = $this->headerScriptPersist->changeScriptTags($content);
        }

        return $content;
    }
}
