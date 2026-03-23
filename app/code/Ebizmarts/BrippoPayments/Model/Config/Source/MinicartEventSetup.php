<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class MinicartEventSetup implements OptionSourceInterface
{
    const CLICK = 'click';
    const HOVER = 'hover';
    const BOTH = 'both';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CLICK,
                'label' => __('Click'),
            ],
            [
                'value' => self::HOVER,
                'label' => __('Hover'),
            ],
            [
                'value' => self::BOTH,
                'label' => __('Both'),
            ]
        ];
    }
}
