<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Indexer for Magento 2 (System)
 */

namespace Amasty\StorelocatorIndexer\Observer;

use Amasty\StorelocatorIndexer\Model\Indexer\Product\IndexBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ProductSave execute when Save Product
 */
class ProductSave implements ObserverInterface
{
    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    public function __construct(IndexBuilder $indexBuilder)
    {
        $this->indexBuilder = $indexBuilder;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($product = $observer->getEvent()->getProduct()) {
            $this->indexBuilder->reindexByProductId($product->getId());
        }
    }
}
