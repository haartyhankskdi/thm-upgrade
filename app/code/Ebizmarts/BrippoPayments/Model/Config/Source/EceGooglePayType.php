<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EceGooglePayType implements OptionSourceInterface
{
    const BUY = 'buy';
    const BOOK = 'book';
    const CHECKOUT = 'checkout';
    const ORDER = 'order';
    const PAY = 'pay';
    const PLAIN = 'plain';
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::BUY,
                'label' => __('Buy'),
            ],
            [
                'value' => self::BOOK,
                'label' => __('Book'),
            ],
            [
                'value' => self::CHECKOUT,
                'label' => __('Checkout'),
            ],
            [
                'value' => self::ORDER,
                'label' => __('Order'),
            ],
            [
                'value' => self::PAY,
                'label' => __('Pay'),
            ],
            [
                'value' => self::PLAIN,
                'label' => __('Plain'),
            ]
        ];
    }
}
