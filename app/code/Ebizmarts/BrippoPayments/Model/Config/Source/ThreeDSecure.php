<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ThreeDSecure implements OptionSourceInterface
{
    const FORCE = 'any';
    const AUTOMATIC = 'automatic';
    const FORCE_FOR_THRESHOLD = 'any_for_threshold';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::FORCE,
                'label' => __('Enforce for all if available'),
            ],
            [
                'value' => self::FORCE_FOR_THRESHOLD,
                'label' => __('Enforce if available only if amount exceeds threshold'),
            ],
            [
                'value' => self::AUTOMATIC,
                'label' => __('Automatic depending on fraud score'),
            ]
        ];
    }
}
