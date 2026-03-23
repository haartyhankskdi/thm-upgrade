<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Test\GraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class XsearchRecentSearchesTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchRecentSearches';

    /**
     * @group amasty_xsearch
     *
     * @magentoConfigFixture base_website amasty_xsearch/recent_searches/enabled 1
     *
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/queries_with_related_terms.php
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/popular_queries.php
     */
    public function testXsearchRecentSearches(): void
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertEquals('recent_searches', $response[self::MAIN_QUERY_KEY]['code']);
        $this->assertGreaterThanOrEqual('4', $response[self::MAIN_QUERY_KEY]['total_count']);
        $this->assertGreaterThanOrEqual(4, count($response[self::MAIN_QUERY_KEY]['items']));
        $this->assertArrayHasKey('name', $response[self::MAIN_QUERY_KEY]['items'][0]);
        $this->assertArrayHasKey('num_results', $response[self::MAIN_QUERY_KEY]['items'][1]);
        $this->assertArrayHasKey('url', $response[self::MAIN_QUERY_KEY]['items'][2]);
    }

    /**
     * Returns GraphQl query string
     */
    private function getQuery(): string
    {
        return <<<QUERY
query {
    xsearchRecentSearches {
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
