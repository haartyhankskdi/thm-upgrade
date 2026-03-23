<?php

declare(strict_types=1);

namespace Mw\Extension\Model;

use Mw\Extension\Api\Data\ReviewInterface;
use Mw\Extension\Validation\ValidationResult;

/**
 * Responsible for Review validation
 * Extension point for base validation
 *
 * @api
 */
interface ReviewValidatorInterface
{
    /**
     * Validate Review
     *
     * @param ReviewInterface $review
     *
     * @return ValidationResult
     */
    public function validate(ReviewInterface $review): ValidationResult;
}