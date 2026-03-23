<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AttributeStatuses implements OptionSourceInterface
{
    public const TEXT = 'text';
    public const BOOLEAN = 'boolean';
    public const MULTISELECT = 'multiselect';
    public const SELECT = 'select';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::TEXT,
                'label' => __('Text Field'),
            ],
            [
                'value' => self::BOOLEAN,
                'label' => __('Yes/No'),
            ],
            [
                'value' => self::MULTISELECT,
                'label' => __('Multiple Select'),
            ],
            [
                'value' => self::SELECT,
                'label' => __('Dropdown'),
            ]
        ];
    }
}
