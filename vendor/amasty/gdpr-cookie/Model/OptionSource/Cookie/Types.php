<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\OptionSource\Cookie;

use Magento\Framework\Data\OptionSourceInterface;

class Types implements OptionSourceInterface
{
    public const TYPE_1ST_PARTY = 1;
    public const TYPE_3ST_PARTY = 2;

    public function toArray(): array
    {
        return [
            self::TYPE_1ST_PARTY => __('1st Party'),
            self::TYPE_3ST_PARTY => __('3rd Party')
        ];
    }

    public function toOptionArray(): array
    {
        $optionArray = [];
        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }

    public function getCookieTypeNameById(?int $typeId): string
    {
        return (string)($this->toArray()[$typeId] ?? '');
    }
}
