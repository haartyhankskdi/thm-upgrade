<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Amasty\Blog\Api\PostRepositoryInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var ModuleListInterface $moduleList */
$moduleList = $objectManager->get(ModuleListInterface::class);

if (array_key_exists('Amasty_Blog', $moduleList->getAll())) {

    /** @var PostRepositoryInterface $postRepository */
    $postRepository = $objectManager->create(PostRepositoryInterface::class);

    $postsUrls = ['post-graph-test-one', 'post-graph-test-two', 'post-graph-test-three'];

    $posts = $postRepository->getPostCollection()->getItems();

    foreach ($posts as $post) {
        if (in_array($post->getDataByKey('url_key'), $postsUrls)) {
            $postRepository->deleteById((int)$post->getDataByKey('post_id'));
        }
    }
}
