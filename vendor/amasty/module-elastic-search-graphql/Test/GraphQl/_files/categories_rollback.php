<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

/** @var GetCategoryByName $getCategoryByName */
$getCategoryByName = $objectManager->create(GetCategoryByName::class);

/** @var  IndexBuilder $indexBuilder */
$indexBuilder = $objectManager->get(IndexBuilder::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$categoryFirst = $getCategoryByName->execute('Category Specific First Xsearch Test');
$categoryTwo = $getCategoryByName->execute('Category Not Specific Second Xsearch Test');
$categoryThird = $getCategoryByName->execute('Category Specific Third Xsearch Test');

try {
    $categoryRepository->delete($categoryFirst);
} catch (NoSuchEntityException $e) {
    //category already deleted.
}

try {
    $categoryRepository->delete($categoryTwo);
} catch (NoSuchEntityException $e) {
    //category already deleted.
}

try {
    $categoryRepository->delete($categoryThird);
} catch (NoSuchEntityException $e) {
    //category already deleted.
}

$indexBuilder->reindexFull();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
