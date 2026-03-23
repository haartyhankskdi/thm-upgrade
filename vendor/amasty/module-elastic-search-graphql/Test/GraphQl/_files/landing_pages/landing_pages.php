<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Amasty\Xlanding\Api\Data\PageInterface;
use Amasty\Xlanding\Api\PageRepositoryInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var ModuleListInterface $moduleList */
$moduleList = $objectManager->get(ModuleListInterface::class);

if (array_key_exists('Amasty_Xlanding', $moduleList->getAll())) {

    /** @var PageRepositoryInterface $pageRepository */
    $pageRepository = $objectManager->create(PageRepositoryInterface::class);

    $pagesData = [
        [
            'title' => 'Graph Test Spec Land Page Title',
            'identifier' => 'page-graph-test-one',
            'page_layout' => '2columns-left',
            'layout_columns_count' => '4',
            'is_active' => 1
        ],
        [
            'title' => 'Graph Test Land Page Two Title',
            'identifier' => 'page-graph-test-two',
            'page_layout' => '2columns-left',
            'layout_columns_count' => '4',
            'is_active' => 1
        ],
        [
            'title' => 'Graph Test Land Page Three Title',
            'identifier' => 'page-graph-test-three',
            'page_layout' => '2columns-left',
            'layout_columns_count' => '4',
            'is_active' => 1
        ]
    ];

    foreach ($pagesData as $pageData) {
        /** @var PageInterface $page */
        $page = $objectManager->create(PageInterface::class);
        $page->setTitle($pageData['title'])
            ->setIdentifier($pageData['identifier'])
            ->setIsActive($pageData['is_active'])
            ->setPageLayout($pageData['page_layout'])
            ->setLayoutColumnsCount($pageData['layout_columns_count'])
            ->setStoreId(1);
        $pageRepository->save($page);
    }
}
