<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\StoreSwitcher;

use Amasty\Storelocator\Model\ConfigProvider;
use Magento\Framework\HTTP\PhpEnvironment\RequestFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcherInterface;

class RewriteLocatorUrl implements StoreSwitcherInterface
{
    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ConfigProvider $configProvider,
        RequestFactory $requestFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->requestFactory = $requestFactory;
        $this->storeManager = $storeManager;
    }

    public function switch(
        StoreInterface $fromStore,
        StoreInterface $targetStore,
        string $redirectUrl
    ): string {
        $request = $this->requestFactory->create(['uri' => $redirectUrl]);
        preg_match('/^([^\/]+)/', trim($request->getPathInfo(), '/'), $urlPath);

        if (isset($urlPath[0]) && $urlPath[0] === $this->configProvider->getUrl($fromStore->getStoreId())) {
            $redirectUrl = urldecode(
                trim($this->storeManager->getStore()->getBaseUrl() . $this->configProvider->getUrl())
            );
        }

        return $redirectUrl;
    }
}
