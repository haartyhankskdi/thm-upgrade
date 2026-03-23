<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Attributes;

/**
 * Class Delete
 */
class Delete extends \Amasty\Storelocator\Controller\Adminhtml\Attributes
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $id = (int)$this->getRequest()->getParam('id');
            $attributeModel = $this->attributeFactory->create();
            $this->attributeResourceModel->load($attributeModel, $id);
            $this->attributeResourceModel->delete($attributeModel);
            $this->messageManager->addSuccessMessage(__('Attribute has been deleted.'));
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
