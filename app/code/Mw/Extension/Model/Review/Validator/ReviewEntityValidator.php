<?php

declare(strict_types=1);

namespace Mw\Extension\Model\Review\Validator;

use Mw\Extension\Api\Data\ReviewInterface;
use Mw\Extension\Validation\ValidationResult;
use Mw\Extension\Validation\ValidationResultFactory;
// use Magento\Framework\Validation\ValidationResultFactory;
use Mw\Extension\Model\ReviewValidatorInterface;

/**
 * Class TitleValidator - - validates review entity
 */
class ReviewEntityValidator implements ReviewValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(ValidationResultFactory $validationResultFactory)
    {
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * Check if review entity has been set
     *
     * @param ReviewInterface $review
     *
     * @return ValidationResult
     */
    public function validate(ReviewInterface $review): ValidationResult
    {
        $value = (string)$review->getReviewEntity();
        $errors = [];

        if (trim($value) === '') {
            $errors[] = __('"%field" can not be empty.', ['field' => ReviewInterface::REVIEW_ENTITY]);
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}