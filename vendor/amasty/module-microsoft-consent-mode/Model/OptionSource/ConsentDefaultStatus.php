<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class ConsentDefaultStatus implements OptionSourceInterface
{
    public const DENIED = 0;
    public const GRANTED = 1;

    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            self::DENIED   => __('Denied'),
            self::GRANTED => __('Granted')
        ];
    }
}
