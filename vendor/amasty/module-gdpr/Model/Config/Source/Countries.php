<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Countries implements OptionSourceInterface
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    private $country;

    public function __construct(\Magento\Directory\Model\Config\Source\Country $country)
    {
        $this->country = $country;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->country->toOptionArray();
    }
}
