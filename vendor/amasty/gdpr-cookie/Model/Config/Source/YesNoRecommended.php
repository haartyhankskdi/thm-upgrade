<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class YesNoRecommended implements ArrayInterface
{
    public const NO = 0;
    public const YES = 1;

    public function toOptionArray(): array
    {
        $optionArray = [];
        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }

    public function toArray(): array
    {
        return [
            self::YES => __('Yes'),
            self::NO => __('No (Recommended)'),
        ];
    }
}
