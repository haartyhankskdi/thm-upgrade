<?php

namespace Mw\Extension\Model;

use Mw\Extension\Api\GetOrderInterface;
use Exception;

class GetOrderData implements GetOrderInterface
{
    protected $order;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $order
    ) {
        $this->order = $order;
    }

    /**
     * Get Invoice data by Order Id
     *
     * @param int $orderId
     * @return array
     */
    public function getInvoiceDataByOrderId($orderId)
    {
        $invoiceCollection = null;
        try {
            $order = $this->order->create()->loadByIncrementId($orderId);
            $invoiceCollection = $order->getInvoiceCollection()->getData();
            $response = [
                'status' => 1,
                'message' => 'success',
                "data" => $invoiceCollection
            ];
        } catch (Exception $exception) {
            $response = [
                'status' => 0,
                'message' => 'Error',
                "data" => $invoiceCollection
            ];
        }
        return json_encode($response);
        exit();
    }
}
