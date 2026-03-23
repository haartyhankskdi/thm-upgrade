<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var CategoryInterfaceFactory $categoryFactory */
$categoryFactory = $objectManager->get(CategoryInterfaceFactory::class);

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

$category = $categoryFactory->create();
$category->setName('Category Specific First Xsearch Test')
    ->setIsActive(true)
    ->setPosition(3)
    ->setDescription('Graph One Descript');
$categoryRepository->save($category);

$categoryTwo = $categoryFactory->create();
$categoryTwo->isObjectNew(true);
$categoryTwo->setName('Category Not Specific Second Xsearch Test')
    ->setIsActive(true)
    ->setPosition(3)
    ->setDescription('Graph Two Descript');
$categoryRepository->save($categoryTwo);

$categoryThird = $categoryFactory->create();
$categoryThird->isObjectNew(true);
$categoryThird->setName('Category Specific Third Xsearch Test')
    ->setIsActive(true)
    ->setPosition(2)
    ->setDescription('Graph Three Descript');
$categoryRepository->save($categoryThird);
