<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Test\GraphQl;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class XsearchRecentlyViewedTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchRecentlyViewed';
    public const CUSTOMER = 'new_customer@example.com';
    public const CUSTOMER_PASS = 'Qwert12345';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var ProductRepositoryInterface $productRepository
     */
    private $productRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @group amasty_xsearch
     *
     * @magentoConfigFixture base_website amasty_xsearch/recently_viewed/enabled 1
     *
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/add_recently_viewed_product.php
     */
    public function testXsearchRecentlyViewed(): void
    {
        $product = $this->productRepository->get('virtual_5');
        $productId = $product->getId();
        $productCreatedAt = $product->getCreatedAt();
        $productUpdatedAt = $product->getUpdatedAt();

        $fields = [
            "id" => $productId,
            "name" => "Virtual Product 5",
            "sku" => "virtual_5",
            "description" => [
                "html" => ""
            ],
            "short_description" => [
                "html" => ""
            ],
            "image" => [
                "label" => "Virtual Product 5"
            ],
            "small_image" => [
                "label" => "Virtual Product 5"
            ],
            "thumbnail" => [
                "label" => "Virtual Product 5"
            ],
            "is_salable" => true,
            "rating_summary" => 0,
            "reviews_count" => 0,
            "url_key" => "virtual-product-5",
            "url_suffix" => ".html",
            "url_rewrites" => [
                [
                    "url" => "virtual-product-5.html"
                ]
            ],
            "attribute_set_id" => 4,
            "options_container" => "container2",
            "created_at" => $productCreatedAt,
            "updated_at" => $productUpdatedAt,
            "type_id" => "virtual",
            "websites" => [
                [
                    "code" => "base"
                ]
            ],
            "media_gallery_entries" => [],
            "price" => [
                "minimalPrice" => [
                    "amount" => [
                        "value" => 5
                    ]
                ]
            ],
            "price_range" => [
                "minimum_price" => [
                    "final_price" => [
                        "value" => 5
                    ]
                ]
            ],
            "categories" => []
        ];

        $query = $this->getQuery();
        $response = $this->graphQlQuery($query, [], '', $this->getHeader());

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertEquals(1, $response[self::MAIN_QUERY_KEY]['total_count']);
        $this->assertEquals(1, count($response[self::MAIN_QUERY_KEY]['items']));
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
    private function getQuery(): string
    {
        return <<<QUERY
query {
    xsearchRecentlyViewed {
        code
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
        total_count
    }
}
QUERY;
    }

    /**
     * @param string $userName
     * @param string $password
     *
     * @return string[]
     * @throws AuthenticationException
     */
    private function getHeader(
        string $userName = self::CUSTOMER,
        string $password = self::CUSTOMER_PASS
    ): array {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($userName, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
