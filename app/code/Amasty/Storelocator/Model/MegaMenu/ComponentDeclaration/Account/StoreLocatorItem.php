<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\MegaMenu\ComponentDeclaration\Account;

use Amasty\Storelocator\Model\ConfigProvider;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;

class StoreLocatorItem extends DataObject
{
    private const DEFALUT_DATA = [
        'id' => 'storelocator',
        'sort_order' => 45
    ];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ConfigProvider $configProvider,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct(array_merge(self::DEFALUT_DATA, $data));
        $this->configProvider = $configProvider;
        $this->urlBuilder = $urlBuilder;
    }

    public function isVisible(): bool
    {
        return $this->configProvider->isAddLinkToToolbar();
    }

    public function getSortOrder(): int
    {
        return (int)$this->getData('sort_order');
    }

    public function getItemData(): array
    {
        return array_merge($this->getData(), [
            'isVisible' => $this->isVisible(),
            'name' => $this->configProvider->getLabel(),
            'url' => $this->urlBuilder->getUrl($this->configProvider->getUrl())
        ]);
    }
}
