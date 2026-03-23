<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\OptionSource\Consent;

use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Framework\Data\OptionSourceInterface;

class From implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => ConsentLogger::FROM_REGISTRATION, 'label'=> __('Registration')],
            ['value' => ConsentLogger::FROM_CHECKOUT, 'label'=> __('Checkout')],
            ['value' => ConsentLogger::FROM_CONTACTUS, 'label'=> __('Contact Us')],
            ['value' => ConsentLogger::FROM_SUBSCRIPTION, 'label'=> __('Newsletter Subscription')],
            ['value' => ConsentLogger::FROM_EMAIL, 'label'=> __('Email')],
            [
                'value' => ConsentLogger::FROM_PRIVACY_SETTINGS,
                'label' => __('Optional Consent at Account Privacy Settings')
            ]
        ];
    }
}
