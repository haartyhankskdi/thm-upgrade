<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CheckoutEmail implements OptionSourceInterface
{
    const EMAIL_CHECKOUT = 'email_checkout';
    const EMAIL_WALLET = 'email_wallet';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::EMAIL_CHECKOUT,
                'label' => __('Use checkout email'),
            ],
            [
                'value' => self::EMAIL_WALLET,
                'label' => __('Use wallet email'),
            ]
        ];
    }
}
