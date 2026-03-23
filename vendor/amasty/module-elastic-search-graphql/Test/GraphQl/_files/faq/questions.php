<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Api\QuestionRepositoryInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var ModuleListInterface $moduleList */
$moduleList = $objectManager->get(ModuleListInterface::class);

if (array_key_exists('Amasty_Faq', $moduleList->getAll())) {

    /** @var QuestionRepositoryInterface $questionRepository */
    $questionRepository = $objectManager->create(QuestionRepositoryInterface::class);

    $questionsData = [
        [
            'title' => 'Question Graph Test AmSpec One',
            'short_answer' => 'Short Graph AmTest Question Answer',
            'answer' => 'Full Question Answer One',
            'url_key' => 'short-question-answer-one',
            'visibility' => 1,
            'status' => 1,
            'store_id' => 1
        ],
        [
            'title' => 'Question Graph Test Am Two',
            'short_answer' => 'Short Graph AmTest Question Answer',
            'answer' => 'Full Question Answer Two',
            'url_key' => 'short-question-answer-two',
            'visibility' => 1,
            'status' => 1,
            'store_id' => 1
        ],
        [
            'title' => 'Question Graph Test Am Three',
            'short_answer' => 'Short Graph AmTest Question Answer',
            'answer' => 'Full Question Answer Three AmSpec',
            'url_key' => 'short-question-answer-three',
            'visibility' => 1,
            'status' => 1,
            'store_id' => 1
        ]
    ];

    foreach ($questionsData as $questionData) {
        /** @var QuestionInterface $question */
        $question = $objectManager->create(QuestionInterface::class);
        $question->setTitle($questionData['title'])
            ->setShortAnswer($questionData['short_answer'])
            ->setAnswer($questionData['answer'])
            ->setVisibility($questionData['visibility'])
            ->setUrlKey($questionData['url_key'])
            ->setStatus($questionData['status'])
            ->setStores('0,1');
        $questionRepository->save($question);
    }
}
