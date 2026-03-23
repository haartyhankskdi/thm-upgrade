<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\Widgets\Block\WidgetConfigs;

use Magento\Framework\Data\OptionSourceInterface;

class CaptionAlignment implements OptionSourceInterface
{
    /***
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'sm:text-left', 'label' => __('Left') ],
            ['value' => 'sm:text-center', 'label' => __('Center')],
            ['value' => 'sm:text-right', 'label' => __('Right')],
        ];
    }
}
