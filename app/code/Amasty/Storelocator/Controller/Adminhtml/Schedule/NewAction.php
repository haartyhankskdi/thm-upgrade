<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Schedule;

class NewAction extends \Amasty\Storelocator\Controller\Adminhtml\Schedule
{
    public function execute()
    {
        return $this->resultForwardFactory->create()->forward('edit');
    }
}
