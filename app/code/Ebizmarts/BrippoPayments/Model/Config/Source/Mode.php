<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Mode implements ArrayInterface
{
    const MODE_TEST = 'test';
    const MODE_LIVE = 'live';
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::MODE_TEST,
                'label' => __('Test'),
            ],
            [
                'value' => self::MODE_LIVE,
                'label' => __('Live'),
            ]
        ];
    }
}
