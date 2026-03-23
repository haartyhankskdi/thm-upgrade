<?php

namespace Mw\Extension\Api;

interface GetOrderInterface
{

    /**
     * @api
     * @param string $orderId
     * @return array
     */
    public function getInvoiceDataByOrderId($orderId);
}
