<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Config\Source;

class ReviewStatuses implements \Magento\Framework\Data\OptionSourceInterface
{
    public const PENDING = 0;

    public const APPROVED = 1;

    public const DECLINED = 2;

    public function toOptionArray()
    {
        return [
            [
                'value' => self::PENDING,
                'label' => __('Pending'),
            ],
            [
                'value' => self::APPROVED,
                'label' => __('Approved'),
            ],
            [
                'value' => self::DECLINED,
                'label' => __('Declined'),
            ],
        ];
    }
}
