<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var ModuleListInterface $moduleList */
$moduleList = $objectManager->get(ModuleListInterface::class);

if (array_key_exists('Amasty_Blog', $moduleList->getAll())) {

    /** @var PostRepositoryInterface $postRepository */
    $postRepository = $objectManager->create(PostRepositoryInterface::class);

    $postsData = [
        [
            'status' => '2',
            'title' => 'Post Graph Spec Test One',
            'url_key' => 'post-graph-test-one',
            'short_content' => 'Short Test Graph Content One',
            'full_content' => 'Full Test Graph Content One',
            'meta_robots' => 'index, follow',
            'display_short_content' => 1,
            'store_id' => 1
        ],
        [
            'status' => '2',
            'title' => 'Post Graph Test Two',
            'url_key' => 'post-graph-test-two',
            'short_content' => 'Short Test Graph Content Two',
            'full_content' => 'Full Test Graph Content Two',
            'meta_robots' => 'index, follow',
            'display_short_content' => 1,
            'store_id' => 1
        ],
        [
            'status' => '2',
            'title' => 'Post Graph Spec Test Three',
            'url_key' => 'post-graph-test-three',
            'short_content' => 'Short Test Graph Content Three',
            'full_content' => 'Full Test Graph Content Three',
            'meta_robots' => 'index, follow',
            'display_short_content' => 1,
            'store_id' => 1
        ]
    ];

    foreach ($postsData as $postData) {
        /** @var PostInterface $post */
        $post = $objectManager->create(PostInterface::class);
        $post->setStatus($postData['status'])
            ->setTitle($postData['title'])
            ->setUrlKey($postData['url_key'])
            ->setShortContent($postData['short_content'])
            ->setFullContent($postData['full_content'])
            ->setDisplayShortContent($postData['display_short_content'])
            ->setStoreId($postData['store_id'])
            ->setMetaRobots($postData['meta_robots']);
        $postRepository->save($post);
    }
}
