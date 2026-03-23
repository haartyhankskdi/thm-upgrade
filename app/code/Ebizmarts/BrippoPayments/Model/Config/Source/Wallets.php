<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Wallets implements OptionSourceInterface
{
    const APPLE_PAY = 'applePay';
    const GOOGLE_PAY = 'googlePay';
    const LINK = 'link';
    const BROWSER_CARD = 'browserCard';
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::APPLE_PAY,
                'label' => __('Apple Pay'),
            ],
            [
                'value' => self::GOOGLE_PAY,
                'label' => __('Google Pay'),
            ],
            [
                'value' => self::LINK,
                'label' => __('Link'),
            ]
        ];
    }
}
