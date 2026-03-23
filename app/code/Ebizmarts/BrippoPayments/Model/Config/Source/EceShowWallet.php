<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EceShowWallet implements OptionSourceInterface
{
    const AUTO = 'auto';
    const ALWAYS = 'always';
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
                'value' => self::ALWAYS,
                'label' => __('Always'),
            ],
            [
                'value' => self::NEVER,
                'label' => __('Never'),
            ]
        ];
    }
}
