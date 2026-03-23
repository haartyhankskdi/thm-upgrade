<?php

namespace Ebizmarts\SagePaySuite\Api;

interface SyncFromApiModelInterface
{
    /**
     * Saves synced order to db
     * @param \Ebizmarts\SagePaySuite\Api\Data\SyncOrderInterface $syncOrderInterface
     */
    public function saveSyncedOrder($syncOrderInterface);

    /**
     * @param \Ebizmarts\SagePaySuite\Api\Data\SyncOrderInterface $order
     * @return \Ebizmarts\SagePaySuite\Api\Data\SyncOrderInterface|null
     */
    public function updateTable($order);

    /**
     * @param $id
     * @return \Ebizmarts\SagePaySuite\Api\Data\SyncOrderInterface
     */
    public function getById($id);

    /**
     * @param $id
     * @return \Ebizmarts\SagePaySuite\Api\Data\SyncOrderInterface
     */
    public function getByOrderId($id);
}
