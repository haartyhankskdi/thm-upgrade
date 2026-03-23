<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class RecoverCheckoutAutomaticNotificationsTimeframe implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('1 hr'),
            ],
            [
                'value' => 3,
                'label' => __('3 hrs')
            ],
            [
                'value' => 6,
                'label' => __('6 hrs')
            ],
            [
                'value' => 12,
                'label' => __('12 hrs')
            ],
            [
                'value' => 24,
                'label' => __('One day')
            ],
            [
                'value' => 48,
                'label' => __('Two days')
            ],
            [
                'value' => 96,
                'label' => __('Four days')
            ],
            [
                'value' => 168,
                'label' => __('One week')
            ]
        ];
    }
}
