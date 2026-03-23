<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\OptionSource\CookieGroup;

use Magento\Framework\Option\ArrayInterface;

class Enabled implements ArrayInterface
{
    public const ENABLED = 1;

    public const DISABLED = 0;

    public function toOptionArray()
    {
        return [
            ['value' => self::DISABLED, 'label' => __('No')],
            ['value' => self::ENABLED, 'label' => __('Yes')]
        ];
    }
}
