<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand Subscription Functionality
 */

namespace Amasty\ShopByBrandSubscriptionFunctionality\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BrandInfoPosition implements OptionSourceInterface
{
    public const AFTER_BRAND_TITLE = 'after_brand_title';
    public const AFTER_BRAND_DESCRIPTION = 'after_brand_decr';
    public const ABOVE_PAGE_FOOTER = 'above_page_footer';
    public const PRODUCT_PAGE_INFO_TAB = 'product_info_tab';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::AFTER_BRAND_TITLE,
                'label' => __('After Brand Title')
            ],
            [
                'value' => self::AFTER_BRAND_DESCRIPTION,
                'label' => __('After Brand Description')
            ],
            [
                'value' => self::ABOVE_PAGE_FOOTER,
                'label' => __('Above Page Footer')
            ],
            [
                'value' => self::PRODUCT_PAGE_INFO_TAB,
                'label' => __('Product Page More Information Tab')
            ]
        ];
    }
}
