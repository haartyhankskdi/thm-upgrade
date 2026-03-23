<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Schedule;

class Delete extends \Amasty\Storelocator\Controller\Adminhtml\Schedule
{
    public function execute()
    {
        $scheduleId = (int)$this->getRequest()->getParam('id');
        if ($scheduleId) {
            try {
                $model = $this->scheduleModel->load($scheduleId);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the schedule.'));

                return $this->_redirect('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete schedule right now. Please review the log and try again.')
                );
                $this->logger->critical($e);

                return $this->_redirect('*/*/edit', ['id' => (int)$this->getRequest()->getParam('id')]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a schedule to delete.'));

        return $this->_redirect('*/*/');
    }
}
