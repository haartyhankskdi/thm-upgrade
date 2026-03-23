<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Amasty\Xsearch\Model\ResourceModel\Slider\RecentlyViewed;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/virtual_product_5_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/new_customer_rollback.php');

$objectManager = Bootstrap::getObjectManager();

/** @var RecentlyViewed $recentlyViewed */
$recentlyViewed = $objectManager->create(RecentlyViewed::class);

$recentlyViewed->deleteOutdatedRecentProductsRowIds(1, 3600);
