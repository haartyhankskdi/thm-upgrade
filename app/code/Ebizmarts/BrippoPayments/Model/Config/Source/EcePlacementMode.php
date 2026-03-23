<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EcePlacementMode implements OptionSourceInterface
{
    const BEGINNING = 'afterBegin';
    const END = 'beforeEnd';
    const BEFORE = 'beforeBegin';
    const AFTER = 'afterEnd';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::BEFORE,
                'label' => __('Before'),
            ],
            [
                'value' => self::BEGINNING,
                'label' => __('At the beginning'),
            ],
            [
                'value' => self::END,
                'label' => __('At the end'),
            ],
            [
                'value' => self::AFTER,
                'label' => __('After'),
            ]
        ];
    }
}
