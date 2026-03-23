<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Indexer for Magento 2 (System)
 */

namespace Amasty\StorelocatorIndexer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Class LocationSave execute when Save Location
 */
class LocationSave implements ObserverInterface
{
    /**
     * @var string[]
     */
    private $indexers;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(
        IndexerRegistry $indexerRegistry,
        array $indexers
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->indexers = $indexers;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($locationModel = $observer->getEvent()->getDataObject()) {
            foreach ($this->indexers as $index) {
                $indexer = $this->indexerRegistry->get($index);
                if (!$indexer->isScheduled()) {
                    $indexer->reindexRow($locationModel->getId());
                }
            }
        }
    }
}
