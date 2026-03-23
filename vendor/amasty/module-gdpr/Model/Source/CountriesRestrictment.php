<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CountriesRestrictment implements OptionSourceInterface
{
    public const ALL_COUNTRIES = 0;

    public const EEA_COUNTRIES = 1;

    public const SPECIFIED_COUNTRIES = 2;

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'label' =>__('All Countries'),
                'value' => self::ALL_COUNTRIES
            ],
            [
                'label' =>__('EEA Countries'),
                'value' => self::EEA_COUNTRIES
            ],
            [
                'label' =>__('Specified Countries'),
                'value' => self::SPECIFIED_COUNTRIES
            ]
        ];
    }
}
