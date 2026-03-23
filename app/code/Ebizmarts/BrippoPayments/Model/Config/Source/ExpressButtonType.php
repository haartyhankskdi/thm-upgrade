<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ExpressButtonType implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'default',
                'label' => __('Default (Only logo)'),
            ],
            [
                'value' => 'book',
                'label' => __('Book'),
            ],
            [
                'value' => 'buy',
                'label' => __('Buy'),
            ],
            [
                'value' => 'donate',
                'label' => __('Donate'),
            ]
        ];
    }
}
