<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\Widgets\Block\Adminhtml;

use Hyva\Widgets\Block\WidgetConfigs\Rows;

class MultiField extends Rows
{
    protected $rows = [
        'thumbnail' => [
            'label' => 'Image',
            'type' => 'image'
        ],
        'title' => [
            'label' => 'Title',
            'type' => 'text'
        ],
        'description' => [
            'label' => 'Description',
            'type' => 'textarea'
        ],
        'button' => [
            'label' => 'Button text',
            'type' => 'text'
        ],
        'button_url' => [
            'label' => 'Button url',
            'type' => 'text'
        ]
    ];
}
