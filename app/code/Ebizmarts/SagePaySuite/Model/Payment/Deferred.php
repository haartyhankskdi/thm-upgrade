<?php

namespace Ebizmarts\SagePaySuite\Model\Payment;

use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface;
use Ebizmarts\SagePaySuite\Helper\Data;
use Magento\Sales\Api\Data\OrderInterface;

class Deferred
{
    /** @var Data $suiteHelper */
    private $suiteHelper;

    public function __construct(
        Data $suiteHelper
    ) {
        $this->suiteHelper = $suiteHelper;
    }

    /**
     * @param OrderInterface $order
     * @param array $result
     * @param float $invoiceAmount
     * @param float $transactionAmount
     * @return void
     */
    public function updatePaymentAdditionalInformation($order, $result, $invoiceAmount, $transactionAmount)
    {
        $transactionId = $this->getTransactionId($result);
        $associatedTransactions = $order->getPayment()->getAdditionalInformation('associatedTransactions');

        if (!empty($associatedTransactions)) {
            $associatedTransactions [$transactionId]= $transactionAmount;
        } else {
            $associatedTransactions  [$transactionId]= $transactionAmount;
            $associatedTransactions  [$order->getPayment()->getLastTransId()]=$invoiceAmount - $transactionAmount;
        }
        $order->getPayment()
            ->setAdditionalInformation('associatedTransactions', $associatedTransactions)
            ->save();
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function shouldAssociateTransaction($order)
    {
        return $order->getPayment()->getTotalReleased() !== null
            && $order->getPayment()->getTotalReleased() > 0;
    }

    /**
     * @param array|PiTransactionResultInterface $result
     * @return array|string|string[]
     */
    private function getTransactionId($result)
    {
        $transactionId = '';
        if (\is_array($result) && isset($result['data'])) {
            if (isset($result['data']['VPSTxId'])) {
                $transactionId = $this->suiteHelper->removeCurlyBraces($result['data']['VPSTxId']);
            }
        } elseif ($result instanceof PiTransactionResultInterface) {
            $transactionId = $result->getTransactionId();
        }

        return $transactionId;
    }
}
