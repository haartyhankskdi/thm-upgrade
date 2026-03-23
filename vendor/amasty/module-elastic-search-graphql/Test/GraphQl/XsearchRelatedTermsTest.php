<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Test\GraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class XsearchRelatedTermsTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchRelatedTerms';

    /**
     * @group amasty_xsearch
     *
     * @magentoConfigFixture base_website amasty_xsearch/general/enable_save_search_input_value 1
     *
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/queries_with_related_terms.php
     */
    public function testXsearchRelatedTerms(): void
    {
        $fields = [
            [
                'count' => 1,
                'name' => 'test'
            ],
            [
                'count' => 1,
                'name' => 'test_graph'
            ]
        ];

        $query = $this->getQuery('test_graph_');
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertArrayHasKey('items', $response[self::MAIN_QUERY_KEY]);
        $this->assertResponseFields($response[self::MAIN_QUERY_KEY]['items'], $fields);
    }

    /**
     * Returns GraphQl query string
     */
    private function getQuery(string $search): string
    {
        return <<<QUERY
query {
    xsearchRelatedTerms(search: "$search") {
        items {
            count
            name
        }
    }
}
QUERY;
    }
}
