<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Import\Validator;

use Magento\Framework\Validator\AbstractValidator;

/**
 * Class AbstractImportValidator
 */
abstract class AbstractImportValidator extends AbstractValidator implements RowValidatorInterface
{
    protected $context;

    public function init($context)
    {
        $this->context = $context;
        return $this;
    }
}
