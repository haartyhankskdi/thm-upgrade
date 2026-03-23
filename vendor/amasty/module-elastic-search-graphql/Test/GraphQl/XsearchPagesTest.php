<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Test\GraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class XsearchPagesTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchPages';

    /**
     * @group amasty_xsearch
     *
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testXsearchPages(): void
    {
        $fieldsQuery = [
            "code" => 'page',
            "items" => [
                    [
                        "name" => 'Cms Page <span class="amsearch-highlight">Design</span> Blank',
                        "url" => "page_design_blank/",
                        "title" => 'Cms Page <span class="amsearch-highlight">Design</span> Blank'
                    ]
                ],
            "total_count" => 1
        ];

        $query = $this->getQuery("Design");
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertResponseFields($response[self::MAIN_QUERY_KEY], $fieldsQuery);
    }

    /**
     * Returns GraphQl query string
     */
    private function getQuery(string $search): string
    {
        return <<<QUERY
query {
    xsearchPages (search: "$search") {
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
