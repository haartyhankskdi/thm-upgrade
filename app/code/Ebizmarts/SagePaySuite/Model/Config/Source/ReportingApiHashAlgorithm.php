<?php
/**
 * Copyright © 2025 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ReportingApiHashAlgorithm implements OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            [
                'value' => 'md5',
                'label' => __('MD5'),
            ],
            [
                'value' => 'sha256',
                'label' => __('SHA-256')
            ]
        ];
    }
}
