<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Amasty Shop By Brand GraphQl for Magento 2 (System)
 */

namespace Amasty\ShopbyBrandGraphQl\Test\GraphQl;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetAmBrandGetMoreFromThisBrandBlockTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'amBrandGetMoreFromThisBrandBlock';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @group amasty_brands
     *
     * @magentoDataFixture Amasty_ShopbyBrandGraphQl::Test/GraphQl/_files/am_products_with_dropdown_attribute.php
     *
     * @magentoConfigFixture base_website amshopby_brand/general/attribute_code am_dropdown_attribute
     * @magentoConfigFixture base_website amshopby_brand/more_from_brand/enable 1
     * @magentoConfigFixture base_website amshopby_brand/more_from_brand/title "More from {brand_name}"
     * @magentoConfigFixture base_website amshopby_brand/more_from_brand/count 7
     */
    public function testAmBrandGetMoreFromThisBrandBlock()
    {
        $product = $this->productRepository->get('am_simple_4');
        $productNamePattern = 'Amasty Simple Product';

        $query = $this->getQuery((int)$product->getId());
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);

        $this->assertArrayHasKey('items', $response[self::MAIN_QUERY_KEY]);
        $this->assertCount(2, $response[self::MAIN_QUERY_KEY]['items']);
        $this->assertStringContainsString($productNamePattern, $response[self::MAIN_QUERY_KEY]['items'][0]['name']);
        $this->assertStringContainsString($productNamePattern, $response[self::MAIN_QUERY_KEY]['items'][1]['name']);

        $this->assertArrayHasKey('title', $response[self::MAIN_QUERY_KEY]);
        $this->assertEquals('"More from G Amasty Option 2"', $response[self::MAIN_QUERY_KEY]['title']);
    }

    /**
     * @param int $productId
     *
     * @return string
     */
    private function getQuery(int $productId): string
    {
        return <<<QUERY
query {
  amBrandGetMoreFromThisBrandBlock(productId: $productId) {
    items {
      name
    }
    title
  }
}
QUERY;
    }
}
