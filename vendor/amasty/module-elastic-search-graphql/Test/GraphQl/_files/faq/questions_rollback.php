<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Amasty\Faq\Api\QuestionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\ModuleListInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var ModuleListInterface $moduleList */
$moduleList = $objectManager->get(ModuleListInterface::class);

if (array_key_exists('Amasty_Faq', $moduleList->getAll())) {

    /** @var QuestionRepositoryInterface $questionRepository */
    $questionRepository = $objectManager->create(QuestionRepositoryInterface::class);

    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    $searchCriteriaBuilder = $objectManager->create(SearchCriteriaBuilder::class);
    $searchCriteria = $searchCriteriaBuilder
        ->addFilter('short_answer', 'Short Graph AmTest Question Answer')
        ->create();

    $questions = $questionRepository->getList($searchCriteria)->getItems();

    foreach ($questions as $question) {
        $questionRepository->delete($question);
    }
}
