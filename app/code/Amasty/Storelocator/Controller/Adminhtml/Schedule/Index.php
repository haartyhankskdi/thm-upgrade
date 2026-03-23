<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Schedule;

use Amasty\Storelocator\Controller\Adminhtml\Schedule;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 */
class Index extends Schedule
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Storelocator::schedules');
        $resultPage->getConfig()->getTitle()->prepend(__('Location Schedules'));

        return $resultPage;
    }
}
