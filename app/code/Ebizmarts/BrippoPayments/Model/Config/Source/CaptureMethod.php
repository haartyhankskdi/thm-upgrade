<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CaptureMethod implements OptionSourceInterface
{
    const AUTOMATIC_CAPTURE = 'automatic';
    const MANUAL_CAPTURE = 'manual';
    const ON_STATUS_CHANGE_CAPTURE = 'manual_onstatuschange';

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
            ],
            [
                'value' => self::ON_STATUS_CHANGE_CAPTURE,
                'label' => __('On Status Change'),
            ]
        ];
    }
}
