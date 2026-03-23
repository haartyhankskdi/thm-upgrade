<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Source;

use Amasty\Gdpr\Model\ConsentLogger;

/**
 * Adding more locations for display in log listing
 */
class CheckboxLocationLog extends CheckboxLocationCombine
{
    public function toOptionArray()
    {
        $locations = parent::toOptionArray();
        $formattedLocations = [
            [
                'value' => ConsentLogger::FROM_PRIVACY_SETTINGS,
                'label' => __('Optional Consent at Account Privacy Settings')
            ],
            [
                'value' => ConsentLogger::PRIVACY_POLICY_POPUP,
                'label' => __('Privacy Policy Popup')
            ],
            [
                'value' => ConsentLogger::FROM_EMAIL,
                'label' => __('Email')
            ]
        ];

        foreach ($locations as $location) {
            if (!is_array($location['value'])) {
                $formattedLocations[] = $location;
                continue;
            }

            foreach ($location['value'] as $combinedLocation) {
                $formattedLocations[] = $combinedLocation;
            }
        }

        return $formattedLocations;
    }
}
