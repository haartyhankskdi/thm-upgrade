<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Plugin\ElasticSearch\Model\Search\GetRequestQuery;

use Amasty\ElasticSearchGraphQl\Plugin\Catalog\Model\ResourceModel\Product\CollectionPlugin;
use Amasty\Xsearch\Model\Config;
use Amasty\Xsearch\Plugin\ElasticSearch\Model\Search\GetRequestQuery\SortingProviderPlugin as XsearchSortingProvider;
use Magento\Framework\App\RequestInterface;

class SortingProviderPlugin extends XsearchSortingProvider
{
    /**
     * @var CollectionPlugin
     */
    private $collectionPlugin;

    public function __construct(
        CollectionPlugin $collectionPlugin,
        RequestInterface $request,
        Config $config
    ) {
        parent::__construct($request, $config);
        $this->collectionPlugin = $collectionPlugin;
    }

    protected function isAvailable(): bool
    {
        return $this->collectionPlugin->getSearchFlag() && $this->getConfig()->isShowOutOfStockLast();
    }
}
