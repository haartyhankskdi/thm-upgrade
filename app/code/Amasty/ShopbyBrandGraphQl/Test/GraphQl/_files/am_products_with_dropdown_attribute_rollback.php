<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Amasty Shop By Brand GraphQl for Magento 2 (System)
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$skus = ['am_simple_1','am_simple_2', 'am_simple_3', 'am_simple_4', 'am_simple_5'];

try {
    foreach ($skus as $sku) {
        $product = $productRepository->get($sku, false, null, true);
        $productRepository->delete($product);
    }
} catch (NoSuchEntityException $e) {
    //product already deleted.
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()->requireDataFixture(
    'Amasty_ShopbyBrandGraphQl::Test/GraphQl/_files/am_add_dropdown_options_brand_settings_rollback.php'
);
Resolver::getInstance()->requireDataFixture(
    'Amasty_ShopbyBrandGraphQl::Test/GraphQl/_files/am_dropdown_attribute_rollback.php'
);

CacheCleaner::cleanAll();
