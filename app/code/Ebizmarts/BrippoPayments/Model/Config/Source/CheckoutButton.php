<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CheckoutButton implements OptionSourceInterface
{
    const BUTTON_WALLET = 'wallet';
    const BUTTON_CHECKOUT_DEFAULT = 'default';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::BUTTON_WALLET,
                'label' => __('Wallet Button'),
            ],
            [
                'value' => self::BUTTON_CHECKOUT_DEFAULT,
                'label' => __('Checkout Default'),
            ]
        ];
    }
}
