<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Location;

use Magento\Framework\App\ResponseInterface;

class Delete extends \Amasty\Storelocator\Controller\Adminhtml\Location
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->locationModel->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the item.'));

                return $this->_redirect('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);

                return $this->_redirect('*/*/edit', ['id' => (int)$this->getRequest()->getParam('id')]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a item to delete.'));

        return $this->_redirect('*/*/');
    }
}
