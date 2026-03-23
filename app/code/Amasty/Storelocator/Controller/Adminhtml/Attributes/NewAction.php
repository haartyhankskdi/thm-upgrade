<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Attributes;

/**
 * Class NewAction
 */
class NewAction extends \Amasty\Storelocator\Controller\Adminhtml\Attributes
{

    public function execute()
    {
        $resultForward = $this->forwardFactory->create();
        $resultForward->forward('edit');

        return $resultForward;
    }
}
