<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Request;

use Amasty\Gdpr\Model\ActionLogger;
use Amasty\Gdpr\Model\Notification\NotificationsApplier;
use Amasty\Gdpr\Model\Notification\NotifiersProvider;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\Collection;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class Send extends RequestProcessAction
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $requestCollectionFactory;

    /**
     * @var ActionLogger
     */
    private $actionLogger;

    /**
     * @var NotificationsApplier
     */
    private $notificationsApplier;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        CollectionFactory $requestCollectionFactory,
        ActionLogger $actionLogger,
        NotificationsApplier $notificationsApplier
    ) {
        parent::__construct($context, $logger);
        $this->filter = $filter;
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->actionLogger = $actionLogger;
        $this->notificationsApplier = $notificationsApplier;
    }

    public function execute()
    {
        $ids = $this->getRequest()->getParam('ids');
        $comment = $this->getRequest()->getParam('comment');

        if ($ids && $comment) {
            /** @var Collection $requestCollection */
            $requestCollection = $this->requestCollectionFactory->create();
            $requestCollection->addFieldToFilter('id', ['in' => explode(',', $ids)]);

            try {
                $action = function ($customerId) use ($comment) {
                    $this->notificationsApplier->apply(
                        NotifiersProvider::EVENT_DENY_DELETION,
                        (int)$customerId,
                        ['comment' => $comment]
                    );
                    $this->actionLogger->logAction('delete_request_denied', $customerId, $comment);
                };

                $customerIds = array_unique($requestCollection->getColumnValues('customer_id'));
                $total = $this->processRequests($requestCollection, $customerIds, $action);
                $this->messageManager->addSuccessMessage(
                    __('%1 email(s) has been sent', $total)
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error has occurred'));
                $this->logger->critical($e);
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*');
    }
}
