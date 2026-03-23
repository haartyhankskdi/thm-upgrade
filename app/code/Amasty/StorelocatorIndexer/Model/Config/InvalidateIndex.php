<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Indexer for Magento 2 (System)
 */

namespace Amasty\StorelocatorIndexer\Model\Config;

use Amasty\StorelocatorIndexer\Model\Indexer\Content\IndexBuilder;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class InvalidateIndex extends Value implements ProcessorInterface
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        IndexerRegistry $indexerRegistry,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->indexerRegistry = $indexerRegistry;
    }

    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->invalidateLocatorContentIndexer();
        }

        return parent::afterSave();
    }

    /**
     * Process config value
     *
     * @param string $value
     * @return string
     */
    public function processValue($value)
    {
        return $value;
    }

    private function invalidateLocatorContentIndexer(): void
    {
        $indexer = $this->indexerRegistry->get(IndexBuilder::INDEXER_ID);
        if ($indexer->getStatus() == StateInterface::STATUS_VALID) {
            $indexer->invalidate();
        }
    }
}
