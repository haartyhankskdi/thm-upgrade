<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Model\Resolver\Product;

use Magento\CatalogGraphQl\Model\Resolver\Products\Query\FieldSelection;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SelectAttributes
{
    /**
     * @var FieldSelection
     */
    private FieldSelection $fieldSelection;

    public function __construct(
        FieldSelection $fieldSelection
    ) {
        $this->fieldSelection = $fieldSelection;
    }

    public function addRequestedColumns(AbstractCollection $collection, ResolveInfo $info): void
    {
        $productsFields = $this->fieldSelection->getProductsFieldSelection($info);
        $collection->addAttributeToSelect($productsFields);
        $this->addMediaGalleryData($collection, $productsFields);
    }

    private function addMediaGalleryData(AbstractCollection $collection, array $productsFields): void
    {
        if (in_array('media_gallery_entries', $productsFields)) {
            $collection->addMediaGalleryData();
        }
    }
}
