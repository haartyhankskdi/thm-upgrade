<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CaptureMethodPayByLink implements OptionSourceInterface
{
    const AUTOMATIC_CAPTURE = 'automatic';
    const MANUAL_CAPTURE = 'manual';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::AUTOMATIC_CAPTURE,
                'label' => __('Automatic'),
            ],
            [
                'value' => self::MANUAL_CAPTURE,
                'label' => __('Manual'),
            ]
        ];
    }
}
