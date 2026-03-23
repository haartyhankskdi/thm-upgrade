<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ConditionType implements OptionSourceInterface
{
    public const NO_CONDITIONS = 0;
    public const PRODUCT_ATTRIBUTE = 1;

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['label' => __('No Conditions'), 'value' => self::NO_CONDITIONS],
            ['label' => __('Product Attribute'), 'value' => self::PRODUCT_ATTRIBUTE],
        ];
    }
}
