<?php

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class CancelPendingPaymentCron
 * @package Ebizmarts\SagePaySuite\Model\Config\Source
 */
class CancelPendingPaymentCron implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::CANCEL_TIMER_15,
                'label' => __('15 minutes'),
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::CANCEL_TIMER_30,
                'label' => __('30 minutes')
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::CANCEL_TIMER_45,
                'label' => __('45 minutes')
            ]
        ];
    }
}
