<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Test\GraphQl;

use Magento\Framework\Module\ModuleListInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class XsearchBrandsTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchBrands';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleList = $objectManager->get(ModuleListInterface::class);

        if (!array_key_exists('Amasty_ShopbyBrand', $this->moduleList->getAll())) {
            $this->markTestSkipped("Module 'Amasty_ShopbyBrand' is not installed.");
        }
    }

    /**
     * @group amasty_xsearch
     *
     * @magentoConfigFixture base_website amshopby_brand/general/attribute_code am_dropdown_attribute
     *
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/brands/am_brand_products.php
     */
    public function testXsearchBrands(): void
    {
        $urlFirst = 'a-amasty-option-1';
        $urlSecond = 'g-amasty-option-2';

        $fieldsQueryOne = [
            "name" => 'A Amasty Option <span class="amsearch-highlight">1</span>',
            "url" => $urlFirst,
            "title" => 'A Amasty Option <span class="amsearch-highlight">1</span>'
        ];
        $fieldsQueryTwo = [
            "name" => '<span class="amsearch-highlight">G</span> Amasty Option 2',
            "url" => $urlSecond,
            "title" => '<span class="amsearch-highlight">G</span> Amasty Option 2'
        ];

        $query = $this->getQuery("G 1");
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertEquals('brand', $response[self::MAIN_QUERY_KEY]['code']);
        $this->assertEquals('2', $response[self::MAIN_QUERY_KEY]['total_count']);
        $this->assertEquals(2, count($response[self::MAIN_QUERY_KEY]['items']));

        if ($response[self::MAIN_QUERY_KEY]['items'][0]['url'] == $urlFirst) {
            $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][0], $fieldsQueryOne);
            $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][1], $fieldsQueryTwo);
        } else {
            $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][0], $fieldsQueryTwo);
            $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][1], $fieldsQueryOne);
        }
    }

    /**
     * Returns GraphQl query string
     */
    private function getQuery(string $search): string
    {
        return <<<QUERY
query {
    xsearchBrands (search: "$search") {
        code
        items {
            name
            url
            title
        }
        total_count
    }
}
QUERY;
    }
}
