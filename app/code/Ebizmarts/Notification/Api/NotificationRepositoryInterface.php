<?php

namespace Ebizmarts\Notification\Api;

interface NotificationRepositoryInterface
{
    /**
     * Create or update a notification.
     *
     * @param \Ebizmarts\Notification\Api\Data\Notification\NotificationInterface $notification
     * @return \Ebizmarts\Notification\Api\Data\Notification\NotificationInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Ebizmarts\Notification\Api\Data\Notification\NotificationInterface $notification);

    /**
     * @param int $id
     * @return \Ebizmarts\Notification\Api\Data\Notification\NotificationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If Notification with the specified ID does not exist.
     */
    public function getById($id);

    /**
     * @param string $notificationId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If Notification with the specified ID does not exist.
     */
    public function getByNotificationId($notificationId);

    /**
     * @param int $id
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException If Notification with the specified ID does not exist.
     */
    public function delete($id);
}
