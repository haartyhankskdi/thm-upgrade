<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Amasty\Xlanding\Api\PageRepositoryInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var ModuleListInterface $moduleList */
$moduleList = $objectManager->get(ModuleListInterface::class);

if (array_key_exists('Amasty_Xlanding', $moduleList->getAll())) {

    /** @var PageRepositoryInterface $pageRepository */
    $pageRepository = $objectManager->create(PageRepositoryInterface::class);

    $pagesUrls = ['page-graph-test-one', 'page-graph-test-two', 'page-graph-test-three'];

    foreach ($pagesUrls as $pagesUrl) {
        $pageRepository->delete($pageRepository->getByUrlKey($pagesUrl));
    }
}
