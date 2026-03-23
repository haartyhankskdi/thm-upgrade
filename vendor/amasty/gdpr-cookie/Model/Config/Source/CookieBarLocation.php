<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CookieBarLocation implements ArrayInterface
{
    /**#@+*/
    public const DISPLAY_AT_FOOTER = 0;

    public const DISPLAY_AT_TOP = 1;

    /**#@-*/

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::DISPLAY_AT_FOOTER => __('Footer'),
            self::DISPLAY_AT_TOP    => __('Top'),
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }
}
