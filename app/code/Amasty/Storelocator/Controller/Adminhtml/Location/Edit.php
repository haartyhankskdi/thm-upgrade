<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Location;

use Amasty\Storelocator\Controller\Adminhtml\Location;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Edit extends Location implements HttpGetActionInterface
{
    public function execute()
    {
        $locationId = (int)$this->getRequest()->getParam('id', 0);
        if ($locationId) {
            try {
                $model = $this->locationModel->load($locationId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage('The schedule no longer exists.');
                return $this->_redirect('*/*/');
            }
        } else {
            $model = $this->locationModel;
        }

        $this->coreRegistry->register('current_amasty_storelocator_location', $model);

        $title = $locationId ? __('Edit Location') : __('New Location');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->getConfig()->getTitle()->prepend($locationId ? $model->getName() : $title);

        return $resultPage;
    }
}
