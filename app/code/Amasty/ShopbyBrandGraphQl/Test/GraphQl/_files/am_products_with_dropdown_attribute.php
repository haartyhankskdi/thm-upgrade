<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Amasty Shop By Brand GraphQl for Magento 2 (System)
 */

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Amasty_ShopbyBrandGraphQl::Test/GraphQl/_files/am_dropdown_attribute.php'
);
Resolver::getInstance()->requireDataFixture(
    'Amasty_ShopbyBrandGraphQl::Test/GraphQl/_files/am_add_dropdown_options_brand_settings.php'
);

/** Create products with attribute option of dropdown type */

$objectManager = Bootstrap::getObjectManager();

/** @var CategorySetup $installer */
$installer = $objectManager->create(CategorySetup::class);

/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);

/** @var Collection $options */
$options = $objectManager->create(Collection::class);

/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

$attribute = $eavConfig->getAttribute(Product::ENTITY, 'am_dropdown_attribute');
$options->setAttributeFilter($attribute->getId());
$optionIds = $options->getAllIds();

/** @var $product Product */

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Amasty Simple Product One')
    ->setSku('am_simple_1')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setCustomAttribute('am_dropdown_attribute', $optionIds[0])
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock'   => 1,
            'qty'                       => 100,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 1,
        ]
    );
$productRepository->save($product);

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Amasty Simple Product Two')
    ->setSku('am_simple_2')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setCustomAttribute('am_dropdown_attribute', $optionIds[1])
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1
        ]
    );
$productRepository->save($product);

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Amasty Simple Product Three')
    ->setSku('am_simple_3')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setCustomAttribute('am_dropdown_attribute', $optionIds[2])
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1
        ]
    );
$productRepository->save($product);

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Amasty Simple Product Four')
    ->setSku('am_simple_4')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setCustomAttribute('am_dropdown_attribute', $optionIds[1])
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1
        ]
    );
$productRepository->save($product);

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Amasty Simple Product Five')
    ->setSku('am_simple_5')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setCustomAttribute('am_dropdown_attribute', $optionIds[1])
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1
        ]
    );
$productRepository->save($product);

CacheCleaner::cleanAll();
