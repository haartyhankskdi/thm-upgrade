<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Magento\Framework\Option\ArrayInterface;

class PaymentElementLabels implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('Above'),
            ],
            [
                'value' => PaymentElement::LABELS_CODE_FLOATING,
                'label' => __('Floating'),
            ]
        ];
    }
}
