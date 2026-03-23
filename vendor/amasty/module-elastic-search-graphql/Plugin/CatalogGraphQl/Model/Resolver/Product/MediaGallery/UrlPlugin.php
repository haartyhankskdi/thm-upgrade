<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Plugin\CatalogGraphQl\Model\Resolver\Product\MediaGallery;

use Amasty\ElasticSearchGraphQl\Model\Resolver\Product\ProductImage as ProductImageResolver;
use Magento\CatalogGraphQl\Model\Resolver\Product\MediaGallery\Url;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

class UrlPlugin
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function afterResolve(
        Url $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        $value = null
    ) {
        $isAmastyQuery = ($value[ProductImageResolver::IS_AMASTY_FLAG] ?? false);
        if ($isAmastyQuery) {
            $result = str_replace($this->storeManager->getStore()->getBaseUrl(), '', $result);
        }

        return $result;
    }
}
