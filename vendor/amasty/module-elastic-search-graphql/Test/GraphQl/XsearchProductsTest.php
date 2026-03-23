<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Test\GraphQl;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class XsearchProductsTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchProducts';

    /**
     * @var ProductRepositoryInterface $productRepository
     */
    private $productRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @group amasty_xsearch
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_options.php
     * @magentoApiDataFixture Magento/Multishipping/Fixtures/simple_product_10.php
     * @magentoApiDataFixture Magento/Multishipping/Fixtures/simple_product_20.php
     *
     */
    public function testXsearchProducts(): void
    {
        $product = $this->productRepository->get('simple_10');
        $productId = $product->getId();
        $productCreatedAt = $product->getCreatedAt();
        $productUpdatedAt = $product->getUpdatedAt();

        $fields = [
            "id" => $productId,
            "name" => "Simple Product 10",
            "sku" => "simple_10",
            "description" => [
                "html" => ""
            ],
            "short_description" => [
                "html" => ""
            ],
            "image" => [
                "label" => "Simple Product 10"
            ],
            "small_image" => [
                "label" => "Simple Product 10"
            ],
            "thumbnail" => [
                "label" => "Simple Product 10"
            ],
            "is_salable" => true,
            "rating_summary" => 0,
            "reviews_count" => 0,
            "url_key" => "simple-product-10",
            "url_suffix" => ".html",
            "url_rewrites" => [
                [
                    "url" => "simple-product-10.html"
                ]
            ],
            "attribute_set_id" => 4,
            "options_container" => "container2",
            "created_at" => $productCreatedAt,
            "updated_at" => $productUpdatedAt,
            "type_id" => "simple",
            "websites" => [
                [
                    "code" => "base"
                ]
            ],
            "media_gallery_entries" => [],
            "price" => [
                "minimalPrice" => [
                    "amount" => [
                        "value" => 10
                    ]
                ]
            ],
            "price_range" => [
                "minimum_price" => [
                    "final_price" => [
                        "value" => 10
                    ]
                ]
            ],
            "categories" => []
        ];

        $query = $this->getQuery('simple_');
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertEquals(2, $response[self::MAIN_QUERY_KEY]['total_count']);
        $this->assertEquals(2, count($response[self::MAIN_QUERY_KEY]['items']));
        $this->assertEquals('product', $response[self::MAIN_QUERY_KEY]['code']);
        $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][0], $fields);
        $this->assertArrayHasKey('url_path', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('special_price', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('special_from_date', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('special_to_date', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('meta_title', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('meta_keyword', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('meta_description', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('new_from_date', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('new_to_date', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('tier_price', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('gift_message_available', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('manufacturer', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('canonical_url', $response[self::MAIN_QUERY_KEY]['items'][0]);
    }

    /**
     * Returns GraphQl query string
     */
    private function getQuery(string $search): string
    {
        return <<<QUERY
query {
    xsearchProducts(search: "$search") {
        code
        total_count
        items {
            id
            name
            sku
            description {
                html
            }
            short_description {
                html
            }
            image {
                label
            }
            small_image {
                label
            }
            thumbnail {
                label
            }
            is_salable
            rating_summary
            reviews_count
            url_key
            url_suffix
            url_path
            url_rewrites {
                url
            }
            special_price
            special_from_date
            special_to_date
            attribute_set_id
            meta_title
            meta_keyword
            meta_description
            new_from_date
            new_to_date
            tier_price
            options_container
            created_at
            updated_at
            type_id
            websites {
                code
            }
            media_gallery_entries {
                label
            }
            price {
                minimalPrice {
                    amount {
                        value
                    }
                }
            }
            price_range {
                minimum_price {
                    final_price {
                        value
                    }
                }
            }
            gift_message_available
            manufacturer
            categories {
                name
            }
            canonical_url
        }
    }
}
QUERY;
    }
}
