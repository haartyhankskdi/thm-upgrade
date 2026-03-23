<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Amasty\Xsearch\Model\ResourceModel\UserSearch as UserSearchResource;
use Amasty\Xsearch\Model\UserSearch;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Amasty\Xsearch\Model\ResourceModel\Query\CollectionFactory as CollectionQueryFactory;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/popular_queries.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/new_customer.php');

$objectManager = Bootstrap::getObjectManager();

/** @var UserSearchResource $userSearchResource */
$userSearchResource = $objectManager->create(UserSearchResource::class);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);

/** @var CollectionQueryFactory $queryCollectionsFactory */
$collectionQueryFactory = $objectManager->create(CollectionQueryFactory::class);

$customerId = $customerRepository->get('new_customer@example.com')->getId();

$collectionQuery = $collectionQueryFactory->create();
$queryFirst = $collectionQuery->addFieldToFilter('main_table.query_text', ['in' => ['test_pop']])
    ->getFirstItem();

$collectionQuerySecond = $collectionQueryFactory->create();
$querySecond = $collectionQuerySecond->addFieldToFilter('main_table.query_text', ['in' => ['pop_graph_']])
    ->getFirstItem();

$searchs = [
    [
        'user_key' => $customerId,
        'query_id' => $queryFirst->getId()
    ],
    [
        'user_key' => $customerId,
        'query_id' => $querySecond->getId()
    ],
];

foreach ($searchs as $search) {
    /** @var UserSearch $userSearch */
    $userSearch = $objectManager->create(UserSearch::class);

    $userSearch->setUserKey($search['user_key'])
        ->setQueryId($search['query_id']);

    $userSearchResource->save($userSearch);
}
