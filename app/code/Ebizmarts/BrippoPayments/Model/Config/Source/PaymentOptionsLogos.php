<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PaymentOptionsLogos implements ArrayInterface
{
    /**
     * Return the available enabled payment method options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'visa', 'label' => __('Visa')],
            ['value' => 'mastercard', 'label' => __('Mastercard')],
            ['value' => 'amex', 'label' => __('Amex')],
            ['value' => 'googlepay', 'label' => __('Google Pay')],
            ['value' => 'applepay', 'label' => __('Apple Pay')],
            ['value' => 'link', 'label' => __('Link')],
            ['value' => 'klarna', 'label' => __('Klarna')],
            ['value' => 'clearpay', 'label' => __('Clear Pay')],
            ['value' => 'pay_by_bank', 'label' => __('Pay by Bank')],
            ['value' => 'billie', 'label' => __('Billie')]
        ];
    }
}
