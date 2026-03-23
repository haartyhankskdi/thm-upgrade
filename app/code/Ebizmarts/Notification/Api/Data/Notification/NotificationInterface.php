<?php

namespace Ebizmarts\Notification\Api\Data\Notification;

interface NotificationInterface
{
    public const NOTIFICATION_ID ='notification_id';
    public const UPDATED_AT ='updated_at';

    /**
     * @return string
     */
    public function getNotificationId();

    /**
     * @param string $notificationId
     * @return void
     */
    public function setNotificationId($notificationId);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt($updatedAt);
}
