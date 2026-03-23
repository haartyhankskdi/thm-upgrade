<?php

namespace Ebizmarts\Notification\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Notification extends AbstractDb
{
    // @codingStandardsIgnoreStart
    public function __construct(
        Context $context,
        $connectionName = 'default'
    ) {
        parent::__construct($context, $connectionName);
    }
    // @codingStandardsIgnoreEnd

    protected function _construct()
    {
        $this->_init('ebizmarts_notifications', 'id');
    }

    /**
     * @param string $notificationId
     * @return array|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByNotificationId($notificationId)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
            ->from($this->getMainTable())
            ->where('notification_id=?', $notificationId);
        return $connection->fetchRow($select);
    }
}
