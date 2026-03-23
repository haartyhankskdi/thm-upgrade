<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Block\View;

use Magento\Framework\View\Element\Template;

/**
 * Class Attributes
 */
class Attributes extends Template
{
    /**
     * Show attributes
     *
     * @return string
     */
    public function toHtml()
    {
        if (!$this->getLocationAttributes()) {
            return '';
        }

        return parent::toHtml();
    }

    public function getLocationAttributes()
    {
        return $this->getLocation()->getAttributes();
    }
}
