<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand Subscription Functionality
 */

namespace Amasty\ShopByBrandSubscriptionFunctionality\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PageLayout implements OptionSourceInterface
{
    public const DEFAULT = 'default';
    public const EMPTY = 'empty';
    public const COLUMN1 = '1column';
    public const COLUMN2LEFT = '2columns-left';
    public const COLUMN2RIGHT = '2columns-right';
    public const COLUMN3 = '3columns';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::DEFAULT,
                'label' => __('Default Category Layout')
            ],
            [
                'value' => self::EMPTY,
                'label' => __('Empty')
            ],
            [
                'value' => self::COLUMN1,
                'label' => __('1 Column')
            ],
            [
                'value' => self::COLUMN2LEFT,
                'label' => __('2 Columns with Left Bar')
            ],
            [
                'value' => self::COLUMN2RIGHT,
                'label' => __('2 Columns with Right Bar')
            ],
            [
                'value' => self::COLUMN3,
                'label' => __('3 Columns')
            ]
        ];
    }
}
