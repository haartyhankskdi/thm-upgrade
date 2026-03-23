<?php

namespace Ebizmarts\Notification\Model;

use Ebizmarts\Notification\Api\NotificationRepositoryInterface;
use Ebizmarts\Notification\Model\Notification as ModelNotification;
use Ebizmarts\Notification\Model\NotificationFactory as ModelNotificationFactory;
use Ebizmarts\Notification\Model\ResourceModel\Notification;
use Ebizmarts\Notification\Model\ResourceModel\NotificationFactory as ResourceNotification;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    /** @var ModelNotificationFactory $notificationFactory */
    private $notificationFactory;

    /** @var LoggerInterface $loggerInterface */
    private $loggerInterface;

    /** @var ResourceNotification $resourceNotification */
    private $resourceNotification;

    /**
     * NotificationRepository constructor
     *
     * @param ModelNotificationFactory $notificationFactory
     * @param LoggerInterface $loggerInterface
     * @param ResourceNotification $resourceNotification
     */
    public function __construct(
        ModelNotificationFactory $notificationFactory,
        LoggerInterface $loggerInterface,
        ResourceNotification $resourceNotification
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->loggerInterface = $loggerInterface;
        $this->resourceNotification = $resourceNotification;
    }

    /**
     * @inheritDoc
     */
    public function save(\Ebizmarts\Notification\Api\Data\Notification\NotificationInterface $notification)
    {
        try {
            /** @var ModelNotification $modelNotification */
            $modelNotification = $this->notificationFactory->create();
            $modelNotification->setNotificationId($notification->getNotificationId());
            $modelNotification->setUpdatedAt($notification->getUpdatedAt());
            $modelNotification->setId($notification->getId());
            // @codingStandardsIgnoreStart
            $modelNotification->getResource()->save($modelNotification);
            // @codingStandardsIgnoreEnd
        } catch (\Exception $exception) {
            $this->loggerInterface->critical($exception);
            throw new LocalizedException(
                __('There was an error saving notification %1', $exception->getMessage())
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        /** @var ModelNotification $modelNotification */
        $modelNotification = $this->notificationFactory->create();
        $modelNotification->load($id);

        if (!$modelNotification->getId()) {
            throw new NoSuchEntityException(__("Notification does not exist."));
        }

        return $modelNotification;
    }

    /**
     * @inheritDoc
     */
    public function getByNotificationId($notificationId)
    {
        /** @var Notification $resource */
        $resource = $this->resourceNotification->create();
        return $resource->getByNotificationId($notificationId);
    }

    /**
     * @inheritDoc
     */
    public function delete($id)
    {
        /** @var ModelNotification $modelNotification */
        $modelNotification = $this->getById($id);

        try {
            $modelNotification->delete();
        } catch (\Exception $exception) {
            throw new NoSuchEntityException(__("Notification could not be deleted."));
        }
    }
}
