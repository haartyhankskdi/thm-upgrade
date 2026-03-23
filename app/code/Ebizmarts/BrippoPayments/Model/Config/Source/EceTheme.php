<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EceTheme implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'stripe',
                'label' => __('Default'),
            ],
            [
                'value' => 'night',
                'label' => __('Night'),
            ],
            [
                'value' => 'flat',
                'label' => __('Flat'),
            ]
        ];
    }
}
