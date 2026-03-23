<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Search\Model\Query;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var State $state */
$state = $objectManager->create(State::class);

$queriesData = [
    [
        'text' => 'test_pop',
        'num_result' => 1,
        'popularity' => 100,
        'display_in_terms' => 1,
        'active' => 1,
        'processed' => 1
    ],
    [
        'text' => 'pop_graph_',
        'num_result' => 8,
        'popularity' => 90,
        'display_in_terms' => 1,
        'active' => 1,
        'processed' => 1
    ],
    [
        'text' => 'pop_am_test',
        'num_result' => 20,
        'popularity' => 50,
        'display_in_terms' => 1,
        'active' => 1,
        'processed' => 1
    ]
];

$state->setAreaCode(Area::AREA_ADMINHTML);

foreach ($queriesData as $queryData) {
    /** @var Query $query */
    $query = $objectManager->create(Query::class);
    $query->setStoreId(1);
    $query->setQueryText($queryData['text'])
        ->setNumResults($queryData['num_result'])
        ->setPopularity($queryData['popularity'])
        ->setDisplayInTerms($queryData['display_in_terms'])
        ->setIsActive($queryData['active'])
        ->setIsProcessed($queryData['processed'])
        ->setRelatedTerms("{}");
    $query->save();
}
