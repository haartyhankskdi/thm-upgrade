<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class GotFrom implements OptionSourceInterface
{
    public const AUTOMATIC = 'automatic';

    public const CUSTOMER_REQUEST = 'customer_request';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Automatic Delete'), 'value' => self::AUTOMATIC],
            ['label' => __('Customer\'s Request'), 'value' => self::CUSTOMER_REQUEST]
        ];
    }
}
