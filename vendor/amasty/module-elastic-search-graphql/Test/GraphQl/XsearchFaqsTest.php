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

class XsearchFaqsTest extends GraphQlAbstract
{
    public const MAIN_QUERY_KEY = 'xsearchFaqs';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleList = $objectManager->get(ModuleListInterface::class);

        if (!array_key_exists('Amasty_Faq', $this->moduleList->getAll())) {
            $this->markTestSkipped("Module 'Amasty_Faq' is not installed.");
        }
    }

    /**
     * @group amasty_xsearch
     *
     * @magentoApiDataFixture Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/faq/questions.php
     */
    public function testXsearchFaqs(): void
    {
        $urlFirst = 'knowledge-base/short-question-answer-one/';
        $urlSecond = 'knowledge-base/short-question-answer-three/';

        $fieldsQueryOne = [
            "name" => 'Question Graph Test <span class="amsearch-highlight">AmSpec</span> One',
            "url" => $urlFirst,
            "title" => 'Question Graph Test <span class="amsearch-highlight">AmSpec</span> One'
        ];
        $fieldsQueryTwo = [
            "name" => 'Question Graph Test Am Three',
            "url" => $urlSecond,
            "title" => 'Question Graph Test Am Three'
        ];

        $query = $this->getQuery("AmSpec");
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_QUERY_KEY, $response);
        $this->assertEquals('faq', $response[self::MAIN_QUERY_KEY]['code']);
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
    xsearchFaqs (search: "$search") {
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
