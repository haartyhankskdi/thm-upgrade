<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Request;

use Amasty\Gdpr\Api\DeleteRequestRepositoryInterface;
use Amasty\Gdpr\Controller\Adminhtml\AbstractRequest;
use Magento\Framework\Controller\ResultFactory;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\Collection;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class Deny extends AbstractRequest
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CollectionFactory
     */
    private $requestCollectionFactory;

    /**
     * @var DeleteRequestRepositoryInterface
     */
    private $requestRepository;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        CollectionFactory $requestCollectionFactory,
        DeleteRequestRepositoryInterface $requestRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->logger = $logger;
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->requestRepository = $requestRepository;
    }

    /**
     * Mass action execution
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /** @var Collection $collection */
        $collection = $this->filter->getCollection($this->requestCollectionFactory->create());

        if ($collection->count() > 0) {
            try {
                /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
                $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

                $resultPage->setActiveMenu('Amasty_Gdpr::requests');
                $resultPage->addBreadcrumb(__('Delete Requests'), __('Delete Requests'));
                $resultPage->getConfig()->getTitle()->prepend(__('Deny Delete Requests'));

                return $resultPage;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error has occurred'));
                $this->logger->critical($e);
            }
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
