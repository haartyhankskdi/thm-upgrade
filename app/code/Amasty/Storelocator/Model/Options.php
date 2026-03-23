<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model;

/**
 * Class Options
 */
class Options extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Storelocator\Model\ResourceModel\Options::class);
        $this->setIdFieldName('value_id');
    }

    public function getOptions()
    {
        return parent::getData('options');
    }
}
