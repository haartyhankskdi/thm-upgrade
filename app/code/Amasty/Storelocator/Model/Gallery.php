<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Gallery
 */
class Gallery extends AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceModel\Gallery::class);
    }
}
