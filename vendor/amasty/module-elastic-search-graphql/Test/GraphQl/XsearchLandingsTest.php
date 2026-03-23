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

class XsearchLandingsTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchLandings';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleList = $objectManager->get(ModuleListInterface::class);

        if (!array_key_exists('Amasty_Xlanding', $this->moduleList->getAll())) {
            $this->markTestSkipped("Module 'Amasty_Xlanding' is not installed.");
        }
    }

    /**
     * @group amasty_xsearch
     *
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/landing_pages/landing_pages.php
     */
    public function testXsearchLandings(): void
    {
        $urlFirst = 'page-graph-test-one/';
        $urlSecond = 'page-graph-test-three/';

        $fieldsQueryOne = [
            "name" => 'Graph Test <span class="amsearch-highlight">Spec</span> Land Page Title',
            "url" => $urlFirst,
            "title" => 'Graph Test <span class="amsearch-highlight">Spec</span> Land Page Title'
        ];
        $fieldsQueryTwo = [
            "name" => 'Graph Test Land Page <span class="amsearch-highlight">Three</span> Title',
            "url" => $urlSecond,
            "title" => 'Graph Test Land Page <span class="amsearch-highlight">Three</span> Title'
        ];

        $query = $this->getQuery("Spec Three");
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertEquals('landing_page', $response[self::MAIN_QUERY_KEY]['code']);
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
    xsearchLandings (search: "$search") {
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
