<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Block\Adminhtml;

/**
 * Class Location
 */
class Location extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'amlocator';
        $this->_controller = 'adminhtml_location';
        $this->_headerText = __('Location Management');
        $this->_addButtonLabel = __('Add Location');
    }
}
