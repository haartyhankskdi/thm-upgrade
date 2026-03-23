<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Schedule;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 */
class MassDelete extends \Amasty\Storelocator\Controller\Adminhtml\Schedule
{
    /**
     * @return mixed
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->scheduleCollection);
        $collectionSize = $collection->getSize();

        foreach ($collection as $schedule) {
            $schedule->delete();
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
