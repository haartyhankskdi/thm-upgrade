<?php

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class SyncOrder
 * @package Ebizmarts\SagePaySuite\Model\Config\Source
 */
class SyncOrder implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::ATTEMPT_NUMBER_ONE,
                'label' => __('1'),
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::ATTEMPT_NUMBER_THREE,
                'label' => __('3')
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::ATTEMPT_NUMBER_FIVE,
                'label' => __('5')
            ]
        ];
    }
}
