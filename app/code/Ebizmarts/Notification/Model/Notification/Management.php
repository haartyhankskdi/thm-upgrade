<?php

namespace Ebizmarts\Notification\Model\Notification;

use Ebizmarts\Notification\Api\Data\Notification\NotificationInterface;
use Ebizmarts\Notification\Api\Data\Notification\NotificationInterfaceFactory;
use Ebizmarts\Notification\Api\NotificationRepositoryInterface;
use Ebizmarts\Notification\Api\NotificationRepositoryInterfaceFactory;
use Magento\Framework\Notification\NotifierInterface as NotifierPool;
use Psr\Log\LoggerInterface;

class Management
{
    public const ERROR_MESSAGE = 'There was an error saving notification: ';
    private const SEVERITY_NOTICE = 'Notice';
    private const SEVERITY_MINOR = 'Minor';
    private const SEVERITY_MAJOR = 'Major';
    private const SEVERITY_CRITICAL = 'Critical';

    /** @var NotifierPool $notifierPool */
    private $notifierPool;

    /** @var NotificationRepositoryInterfaceFactory $notificationRepositoryInterfaceFactory */
    private $notificationRepositoryInterfaceFactory;

    /** @var NotificationInterfaceFactory $notificationInterfaceFactory */
    private $notificationInterfaceFactory;

    /** @var LoggerInterface $loggerInterface */
    private $loggerInterface;

    /***
     * Index action constructor
     *
     * @param NotifierPool $notifierPool
     * @param NotificationRepositoryInterfaceFactory $notificationRepositoryInterfaceFactory
     * @param NotificationInterfaceFactory $notificationInterfaceFactory
     * @param LoggerInterface $loggerInterface
     */
    public function __construct(
        NotifierPool $notifierPool,
        NotificationRepositoryInterfaceFactory $notificationRepositoryInterfaceFactory,
        NotificationInterfaceFactory $notificationInterfaceFactory,
        LoggerInterface $loggerInterface
    ) {
        $this->notifierPool = $notifierPool;
        $this->notificationRepositoryInterfaceFactory = $notificationRepositoryInterfaceFactory;
        $this->notificationInterfaceFactory = $notificationInterfaceFactory;
        $this->loggerInterface = $loggerInterface;
    }

    /**
     * @param array $xmlArray
     * @return void
     */
    public function addNotification($xmlArray)
    {
        $notificationUrl = $xmlArray['url'];
        $severity = $xmlArray['severity'];
        $message = $xmlArray['title'];
        $description = $xmlArray['description'];
        $notificationId =  $xmlArray['id'];
        $updatedAt = $xmlArray['updated_at'];
        $dataNotification = $this->getDataNotification($notificationId);
        if ($this->shouldAddUpdateNotification($dataNotification, $updatedAt)) {
            switch ($severity) {
                case self::SEVERITY_MAJOR:
                    $this->notifierPool->addMajor(
                        $message,
                        $description,
                        $notificationUrl
                    );
                    break;
                case self::SEVERITY_MINOR:
                    $this->notifierPool->addMinor(
                        $message,
                        $description,
                        $notificationUrl
                    );
                    break;
                case self::SEVERITY_NOTICE:
                    $this->notifierPool->addNotice(
                        $message,
                        $description,
                        $notificationUrl
                    );
                    break;
                case self::SEVERITY_CRITICAL:
                    $this->notifierPool->addCritical(
                        $message,
                        $description,
                        $notificationUrl
                    );
                    break;
            }
            $this->processedNotification($dataNotification, $notificationId, $updatedAt);
        }
    }

    /**
     * @param string $notificationId
     * @return bool
     */
    private function getDataNotification($notificationId)
    {
        $data = false;
        try {
            /** @var NotificationRepositoryInterface $notificationRepository */
            $notificationRepository = $this->notificationRepositoryInterfaceFactory->create();
            $data = $notificationRepository->getByNotificationId($notificationId);
        } catch (\Exception $exception) {
            $this->loggerInterface->critical(
                self::ERROR_MESSAGE . $exception->getMessage()
            );
            $this->loggerInterface->critical($exception);
        }

        return $data;
    }

    /**
     * @param array $dataNotification
     * @param string $updatedAt
     * @return bool
     */
    private function shouldAddUpdateNotification($dataNotification, $updatedAt)
    {
        $shouldAddNotification = true;
        try {
            if ($dataNotification !== false) {
                $dataUpdatedAtTime =  strtotime($dataNotification['updated_at']);
                $updatedAtTime = strtotime($updatedAt);
                $shouldAddNotification = $updatedAtTime > $dataUpdatedAtTime;
            }
        } catch (\Exception $exception) {
            $this->loggerInterface->critical(
                self::ERROR_MESSAGE . $exception->getMessage()
            );
            $this->loggerInterface->critical($exception);
        }

        return $shouldAddNotification;
    }

    /**
     * @param array $dataNotification
     * @param string $notificationId
     * @param string $updatedAt
     * @return void
     */
    private function processedNotification($dataNotification, $notificationId, $updatedAt)
    {
        try {
            /** @var NotificationInterface $notificationInterface */
            $notificationInterface = $this->notificationInterfaceFactory->create();
            $id = $dataNotification !== false ? $dataNotification['id'] : null;
            $notificationInterface->setId($id);
            $notificationInterface->setNotificationId($notificationId);
            $notificationInterface->setUpdatedAt($updatedAt);
            /** @var NotificationRepositoryInterface $notificationRepository */
            $notificationRepository = $this->notificationRepositoryInterfaceFactory->create();
            $notificationRepository->save($notificationInterface);
        } catch (\Exception $exception) {
            $this->loggerInterface->critical(
                self::ERROR_MESSAGE . $exception->getMessage()
            );
            $this->loggerInterface->critical($exception);
        }
    }
}
