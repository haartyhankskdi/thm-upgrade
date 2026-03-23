<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Magento\Search\Model\ResourceModel\Query as QueryResource;
use Magento\TestFramework\Helper\Bootstrap;
use Amasty\Xsearch\Model\ResourceModel\Query\CollectionFactory as CollectionQueryFactory;

$objectManager = Bootstrap::getObjectManager();

/** @var QueryResource $queryResource */
$queryResource = $objectManager->create(QueryResource::class);

/** @var CollectionQueryFactory $queryCollectionsFactory */
$collectionQueryFactory = $objectManager->create(CollectionQueryFactory::class);

$collectionQuery = $collectionQueryFactory->create();
$query = $collectionQuery->addFieldToFilter('main_table.query_text', ['in' => ['test_pop']])->getFirstItem();
$queryResource->delete($query);

$collectionQuerySecond = $collectionQueryFactory->create();
$query = $collectionQuerySecond->addFieldToFilter('main_table.query_text', ['in' => ['pop_graph_']])->getFirstItem();
$queryResource->delete($query);

$collectionQueryThird = $collectionQueryFactory->create();
$query = $collectionQueryThird->addFieldToFilter('main_table.query_text', ['in' => ['pop_am_test']])->getFirstItem();
$queryResource->delete($query);
