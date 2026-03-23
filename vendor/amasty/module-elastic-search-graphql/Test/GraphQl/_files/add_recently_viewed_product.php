<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Amasty\Xsearch\Model\ResourceModel\Slider\RecentlyViewed;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/virtual_product_5.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/new_customer.php');

$objectManager = Bootstrap::getObjectManager();

/** @var RecentlyViewed $recentlyViewed */
$recentlyViewed = $objectManager->create(RecentlyViewed::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);

$customerId = $customerRepository->get('new_customer@example.com')->getId();
$productId = $productRepository->get('virtual_5')->getId();

$recentlyViewed->appendNewRecentlyViewedProduct($customerId, $productId, 1);
