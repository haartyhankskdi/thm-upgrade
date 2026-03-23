<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Model\Resolver;

use Amasty\ElasticSearchGraphQl\Model\ConvertProductCollectionToProductDataArray;
use Amasty\ElasticSearchGraphQl\Model\Resolver\Product\SelectAttributes;
use Amasty\ElasticSearchGraphQl\Plugin\Catalog\Model\ResourceModel\Product\CollectionPlugin;
use Amasty\Xsearch\Block\Search\Product as ProductBlock;
use Amasty\Xsearch\Controller\RegistryConstants;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactory;
use RuntimeException;

class Product implements ResolverInterface
{
    public const QUERY_GET_VALUE = 'q';

    public const SEARCH_FIELD = 'search';

    /** @var ProductBlock */
    private $productBlock;

    /** @var RequestInterface */
    private $request;

    /** @var State */
    private $state;

    /** @var Collection */
    private $products = [];

    /** @var LayerResolver */
    private $layerResolver;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $helper;

    /**
     * @var CollectionPlugin
     */
    private $collectionPlugin;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    private $stockHelper;

    /**
     * @var \Magento\CatalogSearch\Helper\Data
     */
    private $searchHelper;

    /**
     * @var ConvertProductCollectionToProductDataArray
     */
    private $convertProductCollectionToProductDataArray;

    /**
     * @var SelectAttributes
     */
    private $selectAttributes;

    public function __construct(
        ProductBlock $productBlock,
        RequestInterface $request,
        State $state,
        LayerResolver $layerResolver,
        \Magento\Framework\Registry $coreRegistry,
        QueryFactory $queryFactory,
        ScopeConfigInterface $scopeConfig,
        \Amasty\Xsearch\Helper\Data $helper,
        CollectionPlugin $collectionPlugin,
        ConvertProductCollectionToProductDataArray $convertProductCollectionToProductDataArray,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\CatalogSearch\Helper\Data $searchHelper,
        ?SelectAttributes $selectAttributes = null // TODO move to not optional
    ) {
        $this->productBlock = $productBlock;
        $this->request = $request;
        $this->state = $state;
        $this->layerResolver = $layerResolver;
        $this->coreRegistry = $coreRegistry;
        $this->queryFactory = $queryFactory;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->collectionPlugin = $collectionPlugin;
        $this->stockHelper = $stockHelper;
        $this->searchHelper = $searchHelper;
        $this->convertProductCollectionToProductDataArray = $convertProductCollectionToProductDataArray;
        // OM for backward compatibility
        $this->selectAttributes = $selectAttributes ?? ObjectManager::getInstance()->get(SelectAttributes::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $this->collectionPlugin->setSearchFlag(true);

        try {
            $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);
        } catch (RuntimeException $e) {
            null;//layer already exists
        }

        $this->request->setQueryValue(self::QUERY_GET_VALUE, $args[self::SEARCH_FIELD]);
        $query = $this->registerQuery((int) $context->getExtensionAttributes()->getStore()->getId());

        $this->state->emulateAreaCode('frontend', function () {
            $this->products = $this->productBlock->getLoadedProductCollection();
        });

        $this->selectAttributes->addRequestedColumns($this->products, $info);

        $query->saveNumResults($this->products->getSize());

        return [
            'items' => $this->getProductData(),
            'total_count' => $this->products->getSize(),
            'code' => 'product'
        ];
    }

    private function registerQuery(int $store): Query
    {
        /** @var Query $query */
        $query = $this->queryFactory->get();
        $query->setStoreId($store);
        $engine = $this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH);
        $query = $this->helper->setStrippedQueryText(
            $query,
            $engine
        );

        if ($query->getQueryText() != '') {
            if ($this->searchHelper->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();
            }
        }

        $this->coreRegistry->register(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY, $query, true);

        return $query;
    }

    private function getProductData(): array
    {
        $this->addStockDataToCollection($this->products);

        return $this->convertProductCollectionToProductDataArray->execute($this->products);
    }

    private function addStockDataToCollection(AbstractCollection $collection): void
    {
        $fromTables = $collection->getSelect()->getPart('from');
        if (!isset($fromTables['stock_status_index'])) {
            $this->stockHelper->addIsInStockFilterToCollection($collection);
        }
    }
}
