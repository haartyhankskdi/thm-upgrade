<?php

namespace Ebizmarts\SagePaySuite\Model\Api;

use Ebizmarts\SagePaySuite\Model\Config;

interface PaymentOperationsInterface
{
    public const DEFERRED_AWAITING_RELEASE = 14;
    public const AUTHENTICATED_AWAITING_AUTHORISE = 15;
    public const SUCCESSFULLY_AUTHORISED   = 16;
    public const SUCCESSFULLY_DEFERRED_ABORTED_BY_VENDOR = 19;
    public const ERROR_SUCCESSFULLY_DEFERRED_ABORTED_BY_VENDOR =
        'Successful DEFERRED transaction ABORTED by the Vendor.';

    /**
     * @param $transactionId
     * @param $amount
     * @param \Magento\Sales\Api\Data\OrderInterface $order.
     * @return mixed
     */
    public function captureDeferredTransaction($transactionId, $amount, \Magento\Sales\Api\Data\OrderInterface $order);

    /**
     * @param $transactionId
     * @param $amount
     * @param \Magento\Sales\Api\Data\OrderInterface $order.
     * @return mixed
     */
    public function refundTransaction($transactionId, $amount, \Magento\Sales\Api\Data\OrderInterface $order);

    /**
     * @param $transactionId
     * @param $amount
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return mixed
     */
    public function authorizeTransaction($transactionId, $amount, \Magento\Sales\Api\Data\OrderInterface $order);

    /**
     * @param $vpstxid
     * @param $quote_data
     * @param $paymentAction
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return mixed
     */
    public function repeatTransaction(
        $vpstxid,
        $quote_data,
        \Magento\Sales\Api\Data\OrderInterface $order,
        $paymentAction = Config::ACTION_REPEAT
    );
}
