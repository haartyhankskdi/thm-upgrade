<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Plugin\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search;
use Amasty\ElasticSearchGraphQl\Plugin\Catalog\Model\ResourceModel\Product\CollectionPlugin;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Api\Search\SearchCriteriaInterface;

class SearchPlugin
{
    /**
     * @var CollectionPlugin
     */
    private $collectionPlugin;

    public function __construct(CollectionPlugin $collectionPlugin)
    {
        $this->collectionPlugin = $collectionPlugin;
    }

    /**
     * @param Search $subject
     * @param SearchCriteriaInterface|array $searchArgument
     * @param ResolveInfo $info
     * @return null|array
     */
    public function beforeGetResult(
        Search $subject,
        $searchArgument,
        $info,
        $context
    ): ?array {
        if ($searchArgument instanceof SearchCriteriaInterface) {
            foreach ($searchArgument->getFilterGroups() as $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    if ($filter->getField() == 'search_term') {
                        $this->collectionPlugin->setSearchFlag(true);
                        break 2;
                    }
                }
            }
        } elseif (is_array($searchArgument) && isset($searchArgument['search'])) {
            $this->collectionPlugin->setSearchFlag(true);
        }
        
        return null;
    }
}
