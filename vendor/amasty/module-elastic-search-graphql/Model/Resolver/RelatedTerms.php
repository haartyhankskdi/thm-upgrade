<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Model\Resolver;

use Amasty\Xsearch\Controller\RegistryConstants;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Search\Model\QueryFactory;

class RelatedTerms implements ResolverInterface
{
    public const QUERY_GET_VALUE = 'q';

    public const SEARCH_FIELD = 'search';

    /** @var RequestInterface */
    private $request;

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
     * @var \Amasty\Xsearch\Model\ResourceModel\Query\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        RequestInterface $request,
        \Magento\Framework\Registry $coreRegistry,
        QueryFactory $queryFactory,
        ScopeConfigInterface $scopeConfig,
        \Amasty\Xsearch\Helper\Data $helper,
        \Amasty\Xsearch\Model\ResourceModel\Query\CollectionFactory $collectionFactory
    ) {
        $this->request = $request;
        $this->coreRegistry = $coreRegistry;
        $this->queryFactory = $queryFactory;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
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
        $this->request->setQueryValue(self::QUERY_GET_VALUE, $args[self::SEARCH_FIELD]);
        $query = $this->registerQuery((int) $context->getExtensionAttributes()->getStore()->getId());

        return [
            'items' => $this->getSearchTermData($query)
        ];
    }

    private function registerQuery(int $store): \Magento\Search\Model\Query
    {
        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();
        $query->setStoreId($store);
        $engine = $this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH);
        $query = $this->helper->setStrippedQueryText(
            $query,
            $engine
        );

        $this->coreRegistry->register(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY, $query, true);

        return $query;
    }

    private function getSearchTermData(\Magento\Search\Model\Query $query): array
    {
        $relatedTerms = $this->collectionFactory->create()->getRelatedTerms($query->getId());
        foreach ($relatedTerms as $term) {
            $data[] = [
                'name' => $term->getQueryText(),
                'count' => (int) $term->getNumResults()
            ];
        }

        return $data ?? [];
    }
}
