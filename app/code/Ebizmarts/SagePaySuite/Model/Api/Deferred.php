<?php

namespace Ebizmarts\SagePaySuite\Model\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Helper\Data;

class Deferred
{
    /** @var Reporting $reportingApi */
    private $reportingApi;

    /** @var Data $suiteHelper */
    private $suiteHelper;

    /**
     * @param Reporting $reportingApi
     * @param Data $suiteHelper
     */
    public function __construct(
        Reporting $reportingApi,
        Data $suiteHelper
    ) {
        $this->reportingApi = $reportingApi;
        $this->suiteHelper = $suiteHelper;
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function getReleasedAmount(OrderInterface $order)
    {
        $this->tryUpdatePaymentDetails($order->getPayment());
        // see if the payment is already released
        $vendorTxCode = $order->getPayment()->getAdditionalInformation("vendorTxCode");
        $transactionList = $this->reportingApi->getTransactionList(
            $vendorTxCode,
            $order->getCreatedAt(),
            $order->getStoreId()
        );
        if ($this->shouldSetTotalReleased($order, $transactionList)) {
            $order->getPayment()->setTotalReleased($transactionList->transactions->transaction->amount)->save();
        }
    }

    /**
     * @param $amount
     * @param OrderInterface $order
     * @return int
     */
    public function getRepeatAmount($amount, OrderInterface $order)
    {
        $total = 0;
        $totalReleased = $order->getPayment()->getTotalReleased();
        if ($totalReleased >= $amount) {
            $order->getPayment()->setTotalReleased($totalReleased-$amount)->save();
        } else {
            $total = $amount - $totalReleased;
            $order->getPayment()->setTotalReleased(0)->save();
        }
        return $total;
    }

    private function tryUpdatePaymentDetails($payment)
    {
        if (empty($payment->getAdditionalInformation("vendorTxCode"))) {
            $vpsTxId = $this->suiteHelper->clearTransactionId($payment->getLastTransId());
            $transactionDetails = $this->reportingApi->getTransactionDetailsByVpstxid($vpsTxId);
            if ($this->issetTransactionDetails($transactionDetails)) {
                $payment->setLastTransId((string)$transactionDetails->vpstxid);
                $payment->setAdditionalInformation('vendorTxCode', (string)$transactionDetails->vendortxcode);
                $payment->setAdditionalInformation('statusDetail', (string)$transactionDetails->status);

                if (isset($transactionDetails->securitykey)) {
                    $payment->setAdditionalInformation('securityKey', (string)$transactionDetails->securitykey);
                }

                if (isset($transactionDetails->threedresult)) {
                    $payment->setAdditionalInformation('threeDStatus', (string)$transactionDetails->threedresult);
                }
                $payment->save();
            }
        }
    }

    /**
     * @return bool
     */
    private function issetTransactionDetails($transactionDetails)
    {
        return isset($transactionDetails->vpstxid)
            && isset($transactionDetails->vendortxcode)
            && isset($transactionDetails->status);
    }

    /**
     * @param OrderInterface $order
     * @param $transactionList
     * @return bool
     */
    private function shouldSetTotalReleased($order, $transactionList)
    {
        return $order->getPayment()->getTotalReleased() === null
            && $transactionList->errorcode == '0000'
            && $transactionList->transactions->totalrows > 0
            && $transactionList->transactions->transaction->released;
    }
}
