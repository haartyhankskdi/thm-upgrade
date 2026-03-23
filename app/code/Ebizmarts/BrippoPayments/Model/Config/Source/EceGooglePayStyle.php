<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EceGooglePayStyle implements OptionSourceInterface
{
    const AUTO = 'auto';
    const BLACK = 'black';
    const WHITE = 'white';
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::AUTO,
                'label' => __('Automatic'),
            ],
            [
                'value' => self::BLACK,
                'label' => __('Black'),
            ],
            [
                'value' => self::WHITE,
                'label' => __('White'),
            ]
        ];
    }
}
