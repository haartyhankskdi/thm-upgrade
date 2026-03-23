<?php

namespace Ebizmarts\SagePaySuite\Api\Data;

interface SyncOrderInterface
{
    public const ORDER_ID = "order_id";
    public const STATUS_CODE = "status_code";
    public const STATUS_DETAIL = 'status_detail';
    public const SYNC_ATTEMPTS = "sync_attempts";

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     * @return void
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getStatusCode();

    /**
     * @param string $statusCode
     * @return void
     */
    public function setStatusCode($statusCode);

    /**
     * @return string
     */
    public function getStatusDetail();

    /**
     * @param string $statusDetail
     * @return void
     */
    public function setStatusDetail($statusDetail);

    /**
     * @return int
     */
    public function getSyncAttempts();

    /**
     * @param int $syncAttempts
     * @return void
     */
    public function setSyncAttempts($syncAttempts);
}
