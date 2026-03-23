<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ProductImage extends \Magento\CatalogGraphQl\Model\Resolver\Product\ProductImage
{
    public const IS_AMASTY_FLAG = 'is_amasty_query';

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        $result = parent::resolve($field, $context, $info, $value, $args);
        $result[self::IS_AMASTY_FLAG] = true;

        return $result;
    }
}
