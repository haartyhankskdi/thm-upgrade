<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PaymentMethods implements ArrayInterface
{
    const CARD = 'card';
    const KLARNA = 'klarna';
    const AFTERPAY_CLEARPAY = 'afterpay_clearpay';
    const PAY_BY_BANK = 'pay_by_bank';
    const BILLIE = 'billie';
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CARD, 'label' => __('Card')],
            ['value' => self::KLARNA, 'label' => __('Klarna')],
            ['value' => self::AFTERPAY_CLEARPAY, 'label' => __('ClearPay')],
            ['value' => self::PAY_BY_BANK, 'label' => __('Pay by Bank')],
            ['value' => self::BILLIE, 'label' => __('Billie')]
        ];
    }
}
