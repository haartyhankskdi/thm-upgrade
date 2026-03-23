<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ConsentLinkType implements OptionSourceInterface
{
    public const PRIVACY_POLICY = 0;

    public const CMS_PAGE = 1;

    /**
     * @return array|void
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('GDPR Privacy policy'),
                'value' => self::PRIVACY_POLICY
            ],
            [
                'label' => __('CMS Page'),
                'value' => self::CMS_PAGE
            ]
        ];
    }
}
