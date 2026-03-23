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

class XsearchBlogsTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchBlogs';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleList = $objectManager->get(ModuleListInterface::class);

        if (!array_key_exists('Amasty_Blog', $this->moduleList->getAll())) {
            $this->markTestSkipped("Module 'Amasty_Blog' is not installed.");
        }
    }

    /**
     * @group amasty_xsearch
     *
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/blog/posts.php
     */
    public function testXsearchBlogs(): void
    {
        $fieldsResponse = [
            'code' => 'blog',
            'items' => [
                [
                    "name" => 'Post Graph Test <span class="amsearch-highlight">Two</span>',
                    "url" => 'blog/post-graph-test-two.html',
                    "title" => 'Post Graph Test <span class="amsearch-highlight">Two</span>'
                ]
            ],
            'total_count' => '1'
        ];

        $query = $this->getQuery("Two");
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertResponseFields($response[self::MAIN_QUERY_KEY], $fieldsResponse);
    }

    /**
     * Returns GraphQl query string
     */
    private function getQuery(string $search): string
    {
        return <<<QUERY
query {
    xsearchBlogs (search: "$search") {
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
