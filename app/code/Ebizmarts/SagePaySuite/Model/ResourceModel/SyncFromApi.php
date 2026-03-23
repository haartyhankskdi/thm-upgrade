<?php

namespace Ebizmarts\SagePaySuite\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SyncFromApi extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct(){
        $this->_init('sagepaysuite_synced_orders', 'id');
    }
    // @codingStandardsIgnoreEnd

    /**
     * Get all synced orders
     *
     * @param \Ebizmarts\SagePaySuite\Api\Data\SyncOrderInterface $object
     * @return mixed
     */
    public function getSyncedOrders(\Ebizmarts\SagePaySuite\Api\Data\SyncOrderInterface $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable());

        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            array_push($data, $row);
        }

        if (count($data)) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $data;
    }

    /**
     * Get synced order by id
     *
     * @param $id
     * @return array
     */
    public function getById($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('id=?', $id);

        $data = $connection->fetchRow($select);

        return $data;
    }

    /**
     * Get synced order by order id
     *
     * @param $orderId
     * @return array
     */
    public function getByOrderId($orderId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('order_id=?', $orderId);

        $data = $connection->fetchRow($select);
        return $data;
    }
}
