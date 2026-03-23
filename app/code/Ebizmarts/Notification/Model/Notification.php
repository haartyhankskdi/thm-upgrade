<?php

namespace Ebizmarts\Notification\Model;

use Ebizmarts\Notification\Api\Data\Notification\NotificationInterface;
use Magento\Framework\Model\AbstractModel;

class Notification extends AbstractModel implements NotificationInterface
{
    protected function _construct()
    {
        $this->_init(\Ebizmarts\Notification\Model\ResourceModel\Notification::class);
    }

    /**
     * @inheritdoc
     */
    public function getNotificationId()
    {
        return (string)$this->getData(NotificationInterface::NOTIFICATION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setNotificationId($notificationId)
    {
        $this->setData(NotificationInterface::NOTIFICATION_ID, $notificationId);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return (string)$this->getData(NotificationInterface::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(NotificationInterface::UPDATED_AT, $updatedAt);
    }
}
