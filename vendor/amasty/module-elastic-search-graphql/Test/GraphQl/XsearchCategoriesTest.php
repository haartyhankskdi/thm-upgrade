<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Test\GraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class XsearchCategoriesTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchCategories';

    /**
     * @group amasty_xsearch
     *
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/categories.php
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/reindex.php
     */
    public function testXsearchCategories(): void
    {
        $nameCatFirstOne = 'Category <span class="amsearch-highlight">Sp</span>ecific ';
        $nameCatFirstTwo = '<span class="amsearch-highlight">Fir</span>st Xsearch Test';
        $nameCatSecondOne = 'Category <span class="amsearch-highlight">Not</span> ';
        $nameCatSecondTwo = '<span class="amsearch-highlight">Sp</span>ecific Second Xsearch Test';

        $descriptionFirst = "Graph One Descript";
        $descriptionSecond = "Graph Two Descript";

        $fieldsQueryFirst = [
            "name" => $nameCatFirstOne . $nameCatFirstTwo,
            "url" => "category-specific-first-xsearch-test.html",
            "description" => $descriptionFirst
        ];
        $fieldsQuerySecond = [
            "name" => $nameCatSecondOne . $nameCatSecondTwo,
            "url" => "category-not-specific-second-xsearch-test.html",
            "description" => $descriptionSecond
        ];

        $query = $this->getQuery("Not Sp Fir");
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertEquals('category', $response[self::MAIN_QUERY_KEY]['code']);
        $this->assertEquals('2', $response[self::MAIN_QUERY_KEY]['total_count']);
        $this->assertEquals(2, count($response[self::MAIN_QUERY_KEY]['items']));

        if ($response[self::MAIN_QUERY_KEY]['items'][0]['description'] == $descriptionFirst) {
            $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][0], $fieldsQueryFirst);
            $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][1], $fieldsQuerySecond);
        } else {
            $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][0], $fieldsQuerySecond);
            $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][1], $fieldsQueryFirst);
        }
    }

    /**
     * Returns GraphQl query string
     */
    private function getQuery(string $search): string
    {
        return <<<QUERY
query {
    xsearchCategories (search: "$search") {
        code
        items {
            name
            url
            description
        }
        total_count
    }
}
QUERY;
    }
}
