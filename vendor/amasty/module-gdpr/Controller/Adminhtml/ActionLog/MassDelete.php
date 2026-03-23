<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\ActionLog;

use Amasty\Gdpr\Controller\Adminhtml\AbstractActionLog;
use Amasty\Gdpr\Model\ResourceModel\ActionLog\CollectionFactory as ActionLogLogCollectionFactory;
use Amasty\Gdpr\Model\ActionLogRepository;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends AbstractActionLog
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var ActionLogLogCollectionFactory
     */
    private $actionLogCollectionFactory;

    /**
     * @var ActionLogRepository
     */
    private $actionLogRepository;

    public function __construct(
        Filter $filter,
        Action\Context $context,
        ActionLogLogCollectionFactory $actionLogCollectionFactory,
        ActionLogRepository $actionLogRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->actionLogCollectionFactory = $actionLogCollectionFactory;
        $this->actionLogRepository = $actionLogRepository;
    }

    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider();
        /** @var \Amasty\Gdpr\Model\ResourceModel\ActionLog\Collection $collection */
        $collection = $this->filter->getCollection($this->actionLogCollectionFactory->create());

        if ($collection->getSize()) {
            foreach ($collection->getItems() as $consentLog) {
                try {
                    $this->actionLogRepository->delete($consentLog);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }

        $this->messageManager->addSuccessMessage(__('Action logs was successfully removed.'));

        return $this->resultRedirectFactory->create()->setPath('*/*');
    }
}
