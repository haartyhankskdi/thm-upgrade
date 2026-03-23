<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\DataCollector\Location;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\ConfigHtmlConverter;
use Amasty\StorelocatorIndexer\Model\Content\LocationContentProvider;
use Magento\Framework\App\ObjectManager;

class HtmlCollector implements LocationCollectorInterface
{
    /**
     * @var ConfigHtmlConverter
     */
    private $configHtmlConverter;

    /**
     * @var LocationContentProvider
     */
    private $locationContentProvider;

    public function __construct(
        ConfigHtmlConverter $configHtmlConverter,
        ?LocationContentProvider $locationContentProvider = null // TODO move to not optional
    ) {
        $this->configHtmlConverter = $configHtmlConverter;
        $this->locationContentProvider =
            $locationContentProvider ?? ObjectManager::getInstance()->get(LocationContentProvider::class);
    }

    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize(): void
    {
    }

    public function collect(LocationInterface $location): void
    {
        $indexContent = $this->locationContentProvider->get((int)$location->getId());
        if ($indexContent) {
            $location->addData($indexContent);
            return;
        }
        $this->configHtmlConverter->setHtml($location);
    }
}
