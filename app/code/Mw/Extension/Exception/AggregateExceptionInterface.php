<?php

declare(strict_types=1);

namespace Mw\Extension\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface AggregateExceptionInterface
 */
interface AggregateExceptionInterface
{
    /**
     * Returns LocalizedException[] array
     *
     * @see the \Magento\Framework\Webapi\Exception which receives $errors as a set of Localized Exceptions
     *
     * @return LocalizedException[]
     */
    public function getErrors();
}