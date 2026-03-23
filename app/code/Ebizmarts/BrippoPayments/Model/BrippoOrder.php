<?php

namespace Ebizmarts\BrippoPayments\Model;

use DateTime;
use DateTimeZone;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Sales\Api\Data\OrderInterface;

class BrippoOrder
{
    /**
     * BRIPPO ORDER STATUSES
     */
    const STATUS_PENDING            = 'brippo_pending';
    const STATUS_AUTHORIZED         = 'brippo_authorized';
    const STATUS_PAYMENT_FAILED     = 'brippo_payment_failed';
    const STATUS_GATEWAY_ERROR      = 'brippo_gateway_error';
    const STATUS_TRYING_TO_RECOVER  = 'brippo_trying_to_recover';
    const STATUS_BLOCKED            = 'brippo_blocked';

    /**
     * @param OrderInterface $order
     * @param string $description
     * @return void
     * @throws Exception
     */
    public static function updateTimeline(OrderInterface $order, string $description = '')
    {
        $payment = $order->getPayment();
        $currentTimeline = $payment->getAdditionalInformation(PaymentMethod::ADDITIONAL_DATA_TIMELINE);
        if (empty($currentTimeline)) {
            $currentTimeline = [];
        }

        $date = new DateTime('now', new DateTimeZone('UTC'));
        $timestamp = $date->getTimestamp();

        $currentTimeline[] = [
            'timestamp' => $timestamp,
            'status' => $order->getStatus(),
            'state' => $order->getState(),
            'description' => $description
        ];
        $payment->setAdditionalInformation(PaymentMethod::ADDITIONAL_DATA_TIMELINE, $currentTimeline)->save();
    }

    /**
     * @param OrderInterface $order
     * @return string[]
     */
    public static function getTimeline(OrderInterface $order)
    {
        $payment = $order->getPayment();
        return $payment->getAdditionalInformation(PaymentMethod::ADDITIONAL_DATA_TIMELINE);
    }
}
