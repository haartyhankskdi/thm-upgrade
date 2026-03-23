<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Api;

use Ebizmarts\SagePaySuite\Model\Payment\Deferred as SagePaymentDeferred;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface;
use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Sage Pay Pi API
 */
class Pi implements PaymentOperationsInterface
{
    /** @var Reporting */
    private $reportingApi;

    /** @var Data */
    private $suiteHelper;

    /** @var PIRest */
    private $piRestApi;
    private $suiteLogger;
    /**
     * @var Deferred
     */
    private $deferred;

    /** @var SagePaymentDeferred $sagePaymentDeferred */
    private $sagePaymentDeferred;

    private $invalidTransStates = [
        8 => 'Transaction CANCELLED by Sage Pay after 15 minutes of inactivity.'
            .'  This is normally because the customer closed their browser.',
        10 => 'Transaction REJECTED by the Fraud Rules you have in place',
        12 => 'Transaction DECLINED by the bank (NOTAUTHED)',
        11 => 'Transaction ABORTED by the Customer on the Payment Pages',
        13 => 'An ERROR occurred at Sage Pay which cancelled this transaction',
        27 => 'DEFERRED transaction that expired before it was RELEASEd or ABORTed',
        18 => 'Transaction VOIDed by the Vendor',
        19 => 'Successful DEFERRED transaction ABORTED by the Vendor',
        30 => 'The transaction failed',
        32 => 'The transaction was aborted by the customer'
    ];

    /**
     * @param Data $suiteHelper
     * @param PIRest $piRestApi
     * @param Reporting $reportingApi
     * @param Logger $logger
     * @param Deferred $deferred
     * @param SagePaymentDeferred $sagePaymentDeferred
     */
    public function __construct(
        Data $suiteHelper,
        PIRest $piRestApi,
        Reporting $reportingApi,
        Logger $logger,
        Deferred $deferred,
        SagePaymentDeferred $sagePaymentDeferred
    ) {
        $this->suiteHelper         = $suiteHelper;
        $this->piRestApi           = $piRestApi;
        $this->piRestApi->setPaymentMethod();
        $this->reportingApi        = $reportingApi;
        $this->suiteLogger         = $logger;
        $this->deferred            = $deferred;
        $this->sagePaymentDeferred = $sagePaymentDeferred;
    }

    public function captureDeferredTransaction($vpsTxId, $amount, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $result = null;

        $this->deferred->getReleasedAmount($order);
        $shouldAssociateTransaction = $this->sagePaymentDeferred->shouldAssociateTransaction($order);

        $vpsTxId = $this->suiteHelper->clearTransactionId($vpsTxId);
        $transaction = $this->reportingApi->getTransactionDetailsByVpstxid($vpsTxId, $order->getStoreId());

        $this->validateTxStateId($transaction);

        $txStateId = (int)$transaction->txstateid;
        if ($txStateId === PaymentOperationsInterface::DEFERRED_AWAITING_RELEASE) {
            $result = $this->piRestApi->release($vpsTxId, $amount);
            $order->getPayment()->setTotalReleased(0)->save();
        } else {
            if ($txStateId === PaymentOperationsInterface::SUCCESSFULLY_AUTHORISED) {
                $total = $this->deferred->getRepeatAmount($amount, $order) * 100;
                if ($total > 0) {
                    $data = [];
                    $data['VendorTxCode'] = $this->suiteHelper->generateVendorTxCode('', Config::ACTION_REPEAT_PI);
                    $data['Description'] = 'REPEAT deferred transaction from Magento.';
                    $data['Currency'] = (string)$transaction->currency;
                    $data['Amount'] = $total;
                    $result = $this->repeatTransaction($vpsTxId, $data, $order, Config::ACTION_REPEAT_PI);
                    if ($shouldAssociateTransaction) {
                        $this->sagePaymentDeferred->updatePaymentAdditionalInformation(
                            $order,
                            $result,
                            $amount,
                            $total / 100
                        );
                    }
                }
            }
        }

        return $result;
    }

    public function repeatTransaction(
        $vpstxid,
        $quote_data,
        \Magento\Sales\Api\Data\OrderInterface $order,
        $paymentAction = Config::ACTION_REPEAT
    ) {
        return $this->piRestApi->repeat(
            $quote_data['VendorTxCode'],
            $vpstxid,
            $quote_data['Currency'],
            $quote_data['Amount'],
            $quote_data['Description']
        );
    }

    public function authorizeTransaction($vpstxid, $amount, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        throw new LocalizedException(__("Not implemented."));
    }

    public function refundTransaction($vpstxid, $amount, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        throw new LocalizedException(__("Not implemented."));
    }

    /**
     * 1 = Transaction failed registration.  Either an INVALID or MALFORMED response was returned
     * 2 = User on Card Selection page
     * 3 = User on the Card Details Entry Page
     * 4 = User on Confirmation Page
     * 5 = Transaction at 3D-Secure Authentication Stage
     * 6 = Transaction sent for Authorisation
     * 7 = Vendor Notified of transaction state at their NotificationURL.  Awaiting response.
     * 8 = Transaction CANCELLED by Sage Pay after 15 minutes of inactivity.
     * This is normally because the customer closed their browser.
     * 9 = Transaction completed but Vendor systems returned INVALID or ERROR in response to notification POST.
     * Transaction CANCELLED by the Vendor.
     * 10 = Transaction REJECTED by the Fraud Rules you have in place
     * 11 = Transaction ABORTED by the Customer on the Payment Pages
     * 12 = Transaction DECLINED by the bank (NOTAUTHED)
     * 13 = An ERROR occurred at Sage Pay which cancelled this transaction
     * 14 = Successful DEFERRED transaction, awaiting RELEASE
     * 15 = Successful AUTHENTICATED transaction, awaiting AUTHORISE
     * 16 = Successfully authorised transaction
     * 17 = Transaction Timed Out at Authorisation Stage
     * 18 = Transaction VOIDed by the Vendor
     * 19 = Successful DEFERRED transaction ABORTED by the Vendor
     * 20 = Transaction has been timed out by Sage Pay
     * 21 = Successfully REGISTERED transaction, awaiting AUTHORISE
     * 22 = AUTHENTICATED or REGISTERED transaction CANCELLED by the Vendor
     * 23 = Transaction could not be settled with the bank and has been failed by the Sage Pay systems
     * 24 = PayPal Transaction Registered
     * 25 = Token Registered
     * 26 = AUTHENTICATE transaction that can no longer be AUTHORISED against.
     * It has either expired, or been fully authorised
     * 27 = DEFERRED transaction that expired before it was RELEASEd or ABORTed
     * 28 = Transaction waiting for authorisation
     * 29 = Successfully authorised transaction
     * 30 = The transaction failed
     * 31 = The transaction failed due to invalid or incomplete data
     * 32 = The transaction was aborted by the customer
     * 33 = Transaction timed out at authorisation stage
     * 34 = A remote ERROR occurred at Sage Pay which cancelled this transaction
     * 35 = A local ERROR occurred at Sage Pay which cancelled this transaction
     * 36 = The transaction could not be sent to the bank and has been failed by the Sage Pay systems
     * 37 = The transaction was declined by the bank
     * 38 = User at bank details page
     * 39 = User at Token Details page
     * 40 = TBC
     * 41 = PPro Transaction CANCELLED by Sage
     * @param $transaction
     *
     * @throws ApiException
     */
    private function validateTxStateId($transaction)
    {
        if (property_exists($transaction, "txstateid")) {
            $txstateid = $transaction->txstateid;
            if (isset($this->invalidTransStates[$txstateid])) {
                throw new ApiException(__('Cannot capture deferred transaction, ' .
                $this->invalidTransStates[$txstateid] . '.'));
            }
        } else {
            throw new ApiException(__('Cannot capture deferred transaction, transaction state is invalid.'));
        }
    }
}
