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
use Amasty\Xsearch\Model\Slider\RecentlyViewed\ProductsProvider;
use Amasty\Xsearch\Model\Slider\SliderProductsProviderInterface;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Slider implements ResolverInterface
{
    /**
     * @var array
     */
    private $products = null;

    /**
     * @var ResolveInfo
     */
    private $info = null;

    /**
     * @var ProductsProvider
     */
    private $productsProvider;

    /**
     * @var State
     */
    private $state;

    /**
     * @var ConvertProductCollectionToProductDataArray
     */
    private $convertProductModelToProductDataArray;

    /**
     * @var SelectAttributes
     */
    private $selectAttributes;

    public function __construct(
        SliderProductsProviderInterface $productsProvider,
        Config $catalogAttributeConfig, // @deprecated use $selectAttributes. TODO remove
        State $state,
        ConvertProductCollectionToProductDataArray $convertProductModelToProductDataArray,
        ?SelectAttributes $selectAttributes = null // TODO move to not optional
    ) {
        $this->productsProvider = $productsProvider;
        $this->state = $state;
        $this->convertProductModelToProductDataArray = $convertProductModelToProductDataArray;
        // OM for backward compatibility
        $this->selectAttributes = $selectAttributes ?? ObjectManager::getInstance()->get(SelectAttributes::class);
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): array
    {
        $this->info = $info;
        $this->state->emulateAreaCode(Area::AREA_FRONTEND, function () {
            $this->products = $this->getProductItems();
        });

        $productItems = $this->products;
        unset($this->products, $this->info);

        return [
            'items' => $productItems,
            'total_count' => count($productItems),
            'code' => 'product'
        ];
    }

    /**
     * Return array of product data arrays
     *
     * @return array[]
     */
    public function getProductItems(): array
    {
        /** @var ProductCollection $productCollection * */
        $productCollection = $this->productsProvider->getProducts();

        if ($this->info !== null) {
            $this->selectAttributes->addRequestedColumns($productCollection, $this->info);
        }

        return $this->convertProductModelToProductDataArray->execute($productCollection);
    }
}
