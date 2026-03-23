<?php

declare(strict_types=1);

namespace Mw\Extension\Model\Review\Validator;

use Mw\Extension\Api\Data\ReviewInterface;
use Mw\Extension\Validation\ValidationResult;
use Mw\Extension\Validation\ValidationResultFactory;
// use Magento\Framework\Validation\ValidationResultFactory;
use Mw\Extension\Model\ReviewValidatorInterface;

/**
 * Class TitleValidator - validates review title
 */
class TitleValidator implements ReviewValidatorInterface
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
     * Check if review title is not empty
     *
     * @param ReviewInterface $review
     *
     * @return ValidationResult
     */
    public function validate(ReviewInterface $review): ValidationResult
    {
        $value = (string)$review->getTitle();
        $errors = [];

        if (trim($value) === '') {
            $errors[] = __('"%field" can not be empty.', ['field' => ReviewInterface::TITLE]);
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
