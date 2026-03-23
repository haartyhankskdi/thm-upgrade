<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Reviews;

use Amasty\Storelocator\Controller\Adminhtml\Reviews;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Edit
 */
class Edit extends Reviews
{
    /**
     * Edit action
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Storelocator::reviews');
        $resultPage->addBreadcrumb(__('Location Reviews'), __('Location Reviews'));
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Location Review'));
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(\Amasty\Storelocator\Block\Adminhtml\Review\Edit::class)
        );

        return $resultPage;
    }
}
