<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CurrencyMode implements ArrayInterface
{
    const MODE_BASE_CURRENCY = 'base';
    const MODE_CURRENCY_SWITCHER = 'switcher';
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::MODE_BASE_CURRENCY,
                'label' => __('Base Currency'),
            ],
            [
                'value' => self::MODE_CURRENCY_SWITCHER,
                'label' => __('Frontend\'s Currency Switcher')
            ]
        ];
    }
}
