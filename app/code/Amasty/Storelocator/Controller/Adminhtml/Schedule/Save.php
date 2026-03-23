<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Schedule;

class Save extends \Amasty\Storelocator\Controller\Adminhtml\Schedule
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $data = $this->getRequest()->getPostValue();
                $scheduleId = (int)$this->getRequest()->getParam('id');
                if ($scheduleId) {
                    $model = $this->scheduleModel->load($scheduleId);
                    if ($scheduleId != $model->getId()) {
                        $this->messageManager->addErrorMessage(__('The wrong item is specified.'));

                        return $this->_redirect('*/*/');
                    }
                }
                if (is_array($data['schedule'])) {
                    $this->scheduleModel->setSchedule($this->serializer->serialize($data['schedule']));
                }
                $this->scheduleModel->setName($this->getRequest()->getParam('name'));
                $this->scheduleModel->save();
                $this->messageManager->addSuccessMessage(__('You saved the schedule.'));
                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/edit', ['id' => $this->scheduleModel->getId()]);
                }

                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->messageManager->addErrorMessage(
                    __($errorMessage)
                );
                $this->logger->critical($e);
                $this->sessionModel->setPageData($data);
            }
        }

        return $this->_redirect('*/*/');
    }
}
