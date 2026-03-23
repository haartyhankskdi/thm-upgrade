<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EceApplePayType implements OptionSourceInterface
{
    const PLAIN = 'plain';
    const BOOK = 'book';
    const BUY = 'buy';
    const CHECKOUT = 'check-out';
    const CONTINUE = 'continue';
    const ORDER = 'order';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::PLAIN,
                'label' => __('Plain'),
            ],
            [
                'value' => self::BOOK,
                'label' => __('Book'),
            ],
            [
                'value' => self::BUY,
                'label' => __('Buy'),
            ],
            [
                'value' => self::CHECKOUT,
                'label' => __('Checkout'),
            ],
            [
                'value' => self::CONTINUE,
                'label' => __('Continue'),
            ],
            [
                'value' => self::ORDER,
                'label' => __('Order'),
            ]
        ];
    }
}
