<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Clarity Consent Mode
 */

namespace Amasty\ClarityConsentMode\ViewModel;

use Amasty\Base\Model\Serializer;
use Amasty\ClarityConsentMode\Model\ConfigProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManager;

class ClarityViewModel implements ArgumentInterface
{
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly Serializer $serializer,
        private readonly StoreManager $storeManager
    ) {
    }

    public function getConsentTypesData(): string
    {
        $result = [];

        foreach ($this->configProvider->getConsentTypes() as $type) {
            $result[$type['consent_type']] = ['default' => $type['default_status'], 'group' => $type['cookie_group']];
        }

        return $this->serializer->serialize($result);
    }

    public function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }
}
