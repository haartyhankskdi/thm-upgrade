<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

namespace Amasty\ElasticSearchGraphQl\Test\GraphQl;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class XsearchBrowsingHistoryTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchBrowsingHistory';
    public const CUSTOMER = 'new_customer@example.com';
    public const CUSTOMER_PASS = 'Qwert12345';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @group amasty_xsearch
     *
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/user_queries.php
     *
     */
    public function testXsearchBrowsingHistory(): void
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

        $query = $this->getQuery();
        $response = $this->graphQlQuery($query, [], '', $this->getHeader());

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertEquals('browsing_history', $response[self::MAIN_QUERY_KEY]['code']);
        $this->assertEquals('2', $response[self::MAIN_QUERY_KEY]['total_count']);
        $this->assertEquals(2, count($response[self::MAIN_QUERY_KEY]['items']));

        if ($response[self::MAIN_QUERY_KEY]['items'][0]['name'] == 'test_pop') {
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
    private function getQuery(): string
    {
        return <<<QUERY
query {
    xsearchBrowsingHistory {
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
