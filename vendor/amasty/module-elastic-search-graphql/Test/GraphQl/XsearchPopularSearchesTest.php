<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Test\GraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class XsearchPopularSearchesTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchPopularSearches';

    /**
     * @group amasty_xsearch
     *
     * @magentoConfigFixture base_website amasty_xsearch/popular_searches/enabled 1
     *
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/popular_queries.php
     */
    public function testXsearchPopularSearches(): void
    {
        $fieldsQueryOne = [
            "name" => "test_pop",
            "num_results" => "1",
            "url" => "catalogsearch/result/?q=test_pop"
        ];
        $fieldsQueryTwo = [
            "name" => "pop_graph_",
            "num_results" => "8",
            "url" => "catalogsearch/result/?q=pop_graph_"
        ];
        $fieldsQueryThree = [
            "name" => "pop_am_test",
            "num_results" => "20",
            "url" => "catalogsearch/result/?q=pop_am_test"
        ];

        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertEquals('popular_searches', $response[self::MAIN_QUERY_KEY]['code']);
        $this->assertGreaterThanOrEqual('3', $response[self::MAIN_QUERY_KEY]['total_count']);
        $this->assertGreaterThanOrEqual(3, count($response[self::MAIN_QUERY_KEY]['items']));
        $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][0], $fieldsQueryOne);
        $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][1], $fieldsQueryTwo);
        $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'][2], $fieldsQueryThree);
    }

    /**
     * Returns GraphQl query string
     */
    private function getQuery(): string
    {
        return <<<QUERY
query {
    xsearchPopularSearches (search:"") {
        code
        items {
            name
            num_results
            url
        }
        total_count
    }
}
QUERY;
    }
}
