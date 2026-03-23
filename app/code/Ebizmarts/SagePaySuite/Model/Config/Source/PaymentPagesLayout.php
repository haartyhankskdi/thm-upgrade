<?php

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\Data\OptionSourceInterface;

class PaymentPagesLayout implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::MODAL,
                'label' => __('Modal as a "LightBox"'),
            ],
            [
                'value' => Config::REDIRECT_TO_SAGEPAY,
                'label' => __('Redirect to Opayo'),
            ]
        ];
    }
}
