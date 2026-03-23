<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Magento\Framework\Option\ArrayInterface;

class PaymentElementTheme implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => PaymentElement::THEME_CODE_STRIPE,
                'label' => __('Default')
            ],
            [
                'value' => PaymentElement::THEME_CODE_NIGHT,
                'label' => __('Night')
            ],
            [
                'value' => PaymentElement::THEME_CODE_FLAT,
                'label' => __('Flat')
            ]
        ];
    }
}
