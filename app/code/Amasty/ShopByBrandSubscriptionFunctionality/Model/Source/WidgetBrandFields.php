<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand Subscription Functionality
 */

namespace Amasty\ShopByBrandSubscriptionFunctionality\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WidgetBrandFields implements OptionSourceInterface
{
    public const SHOW_DESCRIPTION = 'show_description';
    public const ADDITIONAL_BRAND_INFORMATION = 'additional_brand_information';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::SHOW_DESCRIPTION,
                'label' => __('Short Description')
            ],
            [
                'value' => self::ADDITIONAL_BRAND_INFORMATION,
                'label' => __('Additional Brand Information')
            ]
        ];
    }
}
