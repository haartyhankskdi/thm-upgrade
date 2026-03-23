<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EceOrSeparator implements OptionSourceInterface
{
    const NO_SEPARATOR = 'no_separator';
    const BEFORE = 'before';
    const AFTER = 'after';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::NO_SEPARATOR,
                'label' => __('No OR separator'),
            ],
            [
                'value' => self::BEFORE,
                'label' => __('Before'),
            ],
            [
                'value' => self::AFTER,
                'label' => __('After'),
            ]
        ];
    }
}
