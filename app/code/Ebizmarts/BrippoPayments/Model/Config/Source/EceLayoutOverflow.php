<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EceLayoutOverflow implements OptionSourceInterface
{
    const AUTO = 'auto';
    const NEVER = 'never';
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::AUTO,
                'label' => __('Auto'),
            ],
            [
                'value' => self::NEVER,
                'label' => __('Never'),
            ]
        ];
    }
}
