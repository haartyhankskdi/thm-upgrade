<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EceApplePayStyle implements OptionSourceInterface
{
    const AUTO = 'auto';
    const BLACK = 'black';
    const WHITE = 'white';
    const WHITE_OUTLINE = 'white-outline';
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
            ],
            [
                'value' => self::WHITE_OUTLINE,
                'label' => __('White Outline'),
            ]
        ];
    }
}
