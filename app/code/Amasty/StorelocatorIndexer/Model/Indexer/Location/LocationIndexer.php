<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Indexer for Magento 2 (System)
 */

namespace Amasty\StorelocatorIndexer\Model\Indexer\Location;

use Amasty\StorelocatorIndexer\Model\Indexer\AbstractIndexer;
use Magento\Framework\Exception\LocalizedException;

class LocationIndexer extends AbstractIndexer
{
    /**
     * @param int[] $ids
     *
     * @throws LocalizedException
     */
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexByIds($ids);
    }

    /**
     * @param int $id
     *
     * @throws LocalizedException
     */
    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexById($id);
    }
}
