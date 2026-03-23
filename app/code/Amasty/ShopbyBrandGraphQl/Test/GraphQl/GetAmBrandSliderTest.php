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

class GetAmBrandSliderTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'ambrandslider';

    /**
     * @group amasty_brands
     *
     * @magentoDataFixture Amasty_ShopbyBrandGraphQl::Test/GraphQl/_files/am_products_with_dropdown_attribute.php
     *
     * @magentoConfigFixture base_website amshopby_brand/general/attribute_code am_dropdown_attribute
     */
    public function testAmBrandSlider()
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);

        $answerArray = [
            'items' => [
                [
                    'alt' => 'A Amasty Option 1',
                    'label' => 'A Amasty Option 1',
                    'position' => '0',
                    'url' => 'a-amasty-option-1'
                ],
                [
                    'alt' => 'G Amasty Option 2',
                    'label' => 'G Amasty Option 2',
                    'position' => '0',
                    'url' => 'g-amasty-option-2'
                ],
                [
                    'alt' => 'P Amasty Option 3',
                    'label' => 'P Amasty Option 3',
                    'position' => '0',
                    'url' => 'p-amasty-option-3'
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
  ambrandslider {
    items {
      alt
      label
      position
      url
    }
  }
}
QUERY;
    }
}
