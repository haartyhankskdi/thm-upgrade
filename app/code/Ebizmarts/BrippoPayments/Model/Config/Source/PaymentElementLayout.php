<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Magento\Framework\Option\ArrayInterface;

class PaymentElementLayout implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => PaymentElement::LAYOUT_CODE_TABS,
                'label' => __('Tabs'),
            ],
            [
                'value' => PaymentElement::LAYOUT_CODE_ACCORDION,
                'label' => __('Accordion'),
            ]
        ];
    }
}
