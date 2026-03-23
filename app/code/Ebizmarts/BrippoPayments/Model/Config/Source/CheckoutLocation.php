<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CheckoutLocation implements OptionSourceInterface
{
    const LOCATION_PAYMENTS_LIST = 'payments_list';
    const LOCATION_ON_TOP = 'on_top';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::LOCATION_PAYMENTS_LIST,
                'label' => __('Payment Methods List'),
            ],
            [
                'value' => self::LOCATION_ON_TOP,
                'label' => __('On Top'),
            ]
        ];
    }
}
