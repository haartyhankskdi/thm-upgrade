<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class RecoverCheckoutAutomaticNotifications implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Disabled'),
            ],
            [
                'value' => 1,
                'label' => __('Once')
            ],
            [
                'value' => 2,
                'label' => __('Twice')
            ],
            [
                'value' => 3,
                'label' => __('Thrice')
            ],
            [
                'value' => 4,
                'label' => __('Fourfold')
            ],
            [
                'value' => 5,
                'label' => __('Fivefold')
            ]
        ];
    }
}
