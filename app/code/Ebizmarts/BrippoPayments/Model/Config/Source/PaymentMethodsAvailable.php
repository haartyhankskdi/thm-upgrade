<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PaymentMethodsAvailable implements OptionSourceInterface
{
    const AUTOMATIC = 'automatic';
    const MANUAL = 'manual';
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::AUTOMATIC,
                'label' => __('Automatic'),
            ],
            [
                'value' => self::MANUAL,
                'label' => __('Manual'),
            ],
        ];
    }
}
