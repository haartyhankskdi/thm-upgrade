<?php

namespace Ebizmarts\SagePaySuite\Plugin;

use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\EcommerceManagement;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Magento\Quote\Api\CartRepositoryInterface;

class AddDataForFailedTransactionPi
{
    /** @var Reporting */
    private $reportingApi;

    /** @var Logger */
    private $suiteLogger;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    public function __construct(
        Reporting $reportingApi,
        Logger $suiteLogger,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->reportingApi = $reportingApi;
        $this->suiteLogger = $suiteLogger;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param EcommerceManagement $subject
     * @throws ApiException
     */
    public function afterTryToVoidTransactionAndUpdateResult($subject)
    {
        if ($subject->getPayResult() !== null && $subject->isPaymentSuccessful()) {
            return;
        }

        if ($subject->getPayResult() !== null && !$subject->isPaymentSuccessful()) {
            return;
        }

        $transactionId = $subject->getTransactionId();
        if ($this->isValidTransactionId($transactionId)) {
            $quote = $subject->getQuote();
            $transactionDetails = $this->reportingApi->getTransactionDetailsByVpstxid(
                $transactionId,
                $quote->getStoreId()
            );
            if ($transactionDetails !== null) {
                $this->suiteLogger->debugLog(
                    $transactionDetails,
                    [__LINE__, __METHOD__]
                );
            }
            $this->suiteLogger->debugLog(
                'Failed transaction: ' . $transactionId,
                [__LINE__, __METHOD__]
            );
            $this->suiteLogger->debugLog(
                'Vendor Tx Code: ' . $transactionId . ' reserved order id: ' . $quote->getReservedOrderId(),
                [__LINE__, __METHOD__]
            );
            $this->quoteRepository->save($quote);
            $subject->getResult()->setTransactionId($transactionId);
            $encryptedQuoteId = $subject->encryptAndEncode($quote->getId());
            $subject->getResult()->setQuoteId($encryptedQuoteId);
        }
    }

    /**
     * @param string $transactionId
     * @return bool
     */
    private function isValidTransactionId($transactionId)
    {
        return $transactionId !== null && $transactionId !== 'NA';
    }
}
