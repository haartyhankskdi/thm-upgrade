<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Amasty Shop By Brand GraphQl for Magento 2 (System)
 */

namespace Amasty\ShopbyBrandGraphQl\Test\GraphQl;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetAmBrandListTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'ambrandlist';

    /**
     * @group amasty_brands
     *
     * @magentoDataFixture Amasty_ShopbyBrandGraphQl::Test/GraphQl/_files/am_products_with_dropdown_attribute.php
     *
     * @magentoConfigFixture base_website amshopby_brand/general/attribute_code am_dropdown_attribute
     * @magentoConfigFixture base_website amshopby_brand/general/topmenu_enabled 2
     * @magentoConfigFixture base_website amshopby_brand/general/brands_popup 1
     */
    public function testAmBrandList()
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);

        $answerArray = [
            'all_letters' => 'A,G,P',
            'brand_attribute' => [
                'attribute_code' => 'am_dropdown_attribute',
                'attribute_type' => 'String',
                'entity_type' => 'catalog_product',
                'input_type' => 'select'
            ],
            'items' => [
                [
                    'label' => 'A Amasty Option 1'
                ],
                [
                    'label' => 'G Amasty Option 2'
                ],
                [
                    'label' => 'P Amasty Option 3'
                ]
            ]
        ];

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertResponseFields($response[self::MAIN_QUERY_KEY], $answerArray);
    }

    /**
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
query {
  ambrandlist {
    all_letters
    brand_attribute {
      attribute_code
      attribute_type
      entity_type
      input_type
    }
    items {
      label
    }
  }
}
QUERY;
    }
}
