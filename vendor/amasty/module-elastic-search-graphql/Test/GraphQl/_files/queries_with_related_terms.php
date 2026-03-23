<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Search\Model\Query;
use Magento\Search\Model\ResourceModel\Query as QueryResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Serialize\Serializer\Json;

$objectManager = Bootstrap::getObjectManager();

/** @var Json $json */
$json = $objectManager->create(Json::class);

/** @var QueryResource $queryResource */
$queryResource = $objectManager->create(QueryResource::class);

/** @var State $state */
$state = $objectManager->create(State::class);

$queriesData = [
    [
        'text' => 'test',
        'num_result' => 1,
        'popularity' => 21,
        'display_in_terms' => 1,
        'active' => 1,
        'processed' => 1
    ],
    [
        'text' => 'test_graph',
        'num_result' => 1,
        'popularity' => 3,
        'display_in_terms' => 1,
        'active' => 1,
        'processed' => 1
    ],
    [
        'text' => 'test_graph_',
        'num_result' => 3,
        'popularity' => 7,
        'display_in_terms' => 1,
        'active' => 1,
        'processed' => 1
    ]
];

$queriesIds = [];

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
        ->setIsProcessed($queryData['processed']);

    if ($queryData['text'] == 'test_graph_') {
        $query->setRelatedTerms("{\"$queriesIds[0]\":\"0\",\"$queriesIds[1]\":\"1\"}");
    } else {
        $query->setRelatedTerms("{}");
    }

    $query->save();

    $queriesIds[] = (int)$query->getId();
}
