<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ExpressButtonTheme implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'dark',
                'label' => __('Dark'),
            ],
            [
                'value' => 'light',
                'label' => __('Light'),
            ],
            [
                'value' => 'light-outline',
                'label' => __('Light Outline'),
            ]
        ];
    }
}
