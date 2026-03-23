<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ScheduleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @param \Amasty\Storelocator\Api\Data\ScheduleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * @return \Amasty\Storelocator\Api\Data\ScheduleInterface[]
     */
    public function getItems();
}
