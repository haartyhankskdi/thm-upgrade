<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Indexer for Magento 2 (System)
 */

namespace Amasty\StorelocatorIndexer\Model\Content;

use Amasty\StorelocatorIndexer\Model\ResourceModel\LocationContentIndex;

class LocationContentProvider
{
    /**
     * @var LocationContentIndex
     */
    private $contentIndex;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var bool
     */
    private $isContentRequested = false;

    public function __construct(
        LocationContentIndex $contentIndex
    ) {
        $this->contentIndex = $contentIndex;
    }

    public function get(int $locationId): ?array
    {
        if (!$this->isContentRequested) {
            $this->cache = $this->contentIndex->getLocationsContent();
            $this->isContentRequested = true;
        }

        if (array_key_exists($locationId, $this->cache)) {
            $result = $this->cache[$locationId];
            unset($result[LocationContentIndex::LOCATION_ID]);

            return $result;
        }

        return null;
    }

    public function _resetState(): void
    {
        $this->isContentRequested = false;
        $this->cache = [];
    }
}
