<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\ConfigHtmlConverter;

use Amasty\Storelocator\Api\Data\LocationInterface;

interface VariablesRendererInterface
{
    /**
     * @param LocationInterface $location
     * @param string $variable
     * @return string
     */
    public function renderVariable(LocationInterface $location, string $variable): string;
}
