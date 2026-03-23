<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Location;

use Magento\Framework\Controller\ResultFactory;

class MassAction extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
        /** @var \Magento\Ui\Component\MassAction\Filter $filter */
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /** @var $collection \Amasty\Storelocator\Model\ResourceModel\Location\Collection */
        $collection = $this->filter->getCollection($this->locationCollection);

        $collectionSize = $collection->getSize();
        $action = $this->getRequest()->getParam('action');
        if ($collectionSize && in_array($action, ['activate', 'inactivate', 'delete'])) {
            try {
                $collection->walk($action);
                if ($action === 'delete') {
                    $this->messageManager->addSuccessMessage(__('You deleted the location(s).'));
                } else {
                    $this->messageManager->addSuccessMessage(__('You changed the location(s).'));
                }

                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete location(s) right now. Please review the log and try again.').$e->getMessage()
                );
                $this->logger->critical($e);

                return $resultRedirect;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a location(s) to delete.'));

        return $resultRedirect;
    }
}
