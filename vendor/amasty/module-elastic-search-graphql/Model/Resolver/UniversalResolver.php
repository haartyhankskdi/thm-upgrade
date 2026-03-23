<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Model\Resolver;

use Amasty\Xsearch\Block\Search\AbstractSearch;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use RuntimeException;

class UniversalResolver implements ResolverInterface
{
    public const QUERY_GET_VALUE = 'q';

    public const SEARCH_FIELD = 'search';

    /** @var RequestInterface */
    private $request;

    /** @var State */
    private $state;

    /** @var array */
    private $items = [];

    /** @var LayerResolver */
    private $layerResolver;

    /**
     * @var AbstractSearch
     */
    private $searchBlock;

    /**
     * @var string
     */
    private $code;

    public function __construct(
        RequestInterface $request,
        State $state,
        LayerResolver $layerResolver,
        AbstractSearch $searchBlock,
        $code = ''
    ) {
        $this->request = $request;
        $this->state = $state;
        $this->layerResolver = $layerResolver;
        $this->searchBlock = $searchBlock;
        $this->code = $code;
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
        try {
            $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);
        } catch (RuntimeException $e) {
            null;//layer already exists
        }

        if (isset($args[self::SEARCH_FIELD]) && !empty($args[self::SEARCH_FIELD])) {
            $this->request->setQueryValue(self::QUERY_GET_VALUE, $args[self::SEARCH_FIELD]);
        }

        $this->state->emulateAreaCode('frontend', function () {
            $this->items = $this->searchBlock->getResults();
        });

        return [
            'items' => $this->items,
            'total_count' => $this->searchBlock->getNumResults() ?: count($this->items),
            'code' => $this->code
        ];
    }
}
