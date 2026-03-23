<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CheckoutOnTopValidationMode implements OptionSourceInterface
{
    const CHECKOUT_DEFAULT = 'default';
    const ONLY_AGREEMENTS = 'only-agreements';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CHECKOUT_DEFAULT,
                'label' => __('Default'),
            ],
            [
                'value' => self::ONLY_AGREEMENTS,
                'label' => __('Only Agreements'),
            ]
        ];
    }
}
