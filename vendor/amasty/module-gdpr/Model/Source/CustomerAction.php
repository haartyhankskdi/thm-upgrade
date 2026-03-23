<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CustomerAction implements OptionSourceInterface
{
    public const ACCEPT = 1;
    public const DECLINE = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' =>__('Accept'),
                'value' => self::ACCEPT
            ],
            [
                'label' =>__('Decline'),
                'value' => self::DECLINE
            ]
        ];
    }
}
