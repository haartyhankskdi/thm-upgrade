<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Helper\Fraud;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use \Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use \Ebizmarts\SagePaySuite\Model\ResourceModel\Fraud as FraudModel;
use \Ebizmarts\SagePaySuite\Helper\Data;
use \Ebizmarts\SagePaySuite\Model\Api\Reporting;

class Cron
{
    public const CANCELABLE_TXSTATEIDS = [
        1  => 'Transaction failed registration. Either an INVALID or MALFORMED response was returned',
        8  => 'Transaction CANCELLED by MyOpayo after 15 minutes of inactivity.'.
            ' This is normally because the customer closed their browser.',
        9  => 'Transaction completed but Vendor systems returned INVALID or ERROR in response to notification POST. ' .
            'Transaction CANCELLED by the Vendor.',
        10 => 'Transaction REJECTED by the Fraud Rules you have in place',
        11 => 'Transaction ABORTED by the Customer on the Payment Pages',
        12 => 'Transaction DECLINED by the bank (NOTAUTHED)',
        13 => 'An ERROR occurred at MyOpayo which cancelled this transaction',
        17 => 'Transaction Timed Out at Authorisation Stage',
        18 => 'Transaction VOIDed by the Vendor',
        19 => 'Successful DEFERRED transaction ABORTED by the Vendor',
        20 => 'Transaction has been timed out by MyOpayo',
        22 => 'AUTHENTICATED or REGISTERED transaction CANCELLED by the Vendor',
        23 => 'Transaction could not be settled with the bank and has been failed by the MyOpayo systems',
        26 => 'AUTHENTICATE transaction that can no longer be AUTHORISED against. ' .
            'It has either expired, or been fully authorised',
        27 => 'DEFERRED transaction that expired before it was RELEASEd or ABORTed',
        30 => 'The transaction failed',
        31 => 'The transaction failed due to invalid or incomplete data',
        32 => 'The transaction was aborted by the customer',
        33 => 'Transaction timed out at authorisation stage',
        34 => 'A remote ERROR occurred at MyOpayo which cancelled this transaction',
        35 => 'A local ERROR occurred at MyOpayo which cancelled this transaction',
        36 => 'The transaction could not be sent to the bank and has been failed by the MyOpayo systems',
        37 => 'The transaction was declined by the bank',
        41 => 'PPro Transaction CANCELLED by MyOpayo'
    ];
    public const CORRECT_TXSTATEIDS = [
        16 => 'Successfully authorised transaction',
        29 => 'Successfully authorised transaction'
    ];
    private const TRANSACTION_NOT_FOUND = "0043";

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
         * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface;
     */
    private $transactionRepository;

    /**
     * @var Fraud
     */
    private $fraudHelper;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\ResourceModel\Fraud;
     */
    private $fraudModel;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var Data
     */
    private $suiteHelper;

    /**
     * @var Reporting
     */
    private $reportingApi;

    /**
     * Cron constructor.
     * @param Logger $suiteLogger
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     * @param CollectionFactory $orderCollectionFactory
     * @param TransactionRepositoryInterface $transactionRepository
     * @param Fraud $fraudHelper
     * @param FraudModel $fraudModel
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param Data $suiteHelper
     * @param Reporting $reportingApi
     */
    public function __construct(
        Logger $suiteLogger,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        ObjectManagerInterface $objectManager,
        Config $config,
        CollectionFactory $orderCollectionFactory,
        TransactionRepositoryInterface $transactionRepository,
        Fraud $fraudHelper,
        FraudModel $fraudModel,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $filterBuilder,
        Data $suiteHelper,
        Reporting $reportingApi
    ) {
        $this->suiteLogger            = $suiteLogger;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->objectManager          = $objectManager;
        $this->config                 = $config;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->transactionRepository  = $transactionRepository;
        $this->fraudHelper            = $fraudHelper;
        $this->fraudModel             = $fraudModel;
        $this->criteriaBuilder        = $criteriaBuilder;
        $this->filterBuilder          = $filterBuilder;
        $this->suiteHelper            = $suiteHelper;
        $this->reportingApi           = $reportingApi;
    }

    /**
     * Cancel Sage Pay orders in "pending payment" state after a period of time
     */
    public function cancelPendingPaymentOrders()
    {
        $cancelTimer = $this->config->getAdvancedValue('cancel_pending_payment_cron');
        $orderIds = $this->fraudModel->getOrderIdsToCancel($cancelTimer);

        if (!count($orderIds)) {
            return $this;
        }

        $orderCollection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => implode(',', $orderIds)])
            ->load();

        /** @var $_order Order */
        foreach ($orderCollection as $_order) {
            $this->processOrder($_order);
        }
        return $this;
    }

    /**
     * Processes an individual order based on its payment transaction status.
     * @param $order
     * @return void
     */
    private function processOrder($order)
    {
        $orderId = $order->getEntityId();

        try {
            $payment = $order->getPayment();
            if ($payment === null) {
                $this->logErrorPaymentNotFound($orderId);
                return;
            }

            $transactionId = $this->suiteHelper->clearTransactionId($payment->getLastTransId());
            $transactionDetails = $this->getTransactionDetails($transactionId, $order, $payment);
            if (!$transactionDetails || !property_exists($transactionDetails, 'txstateid')) {
                return;
            }

            $this->handleTransactionBasedOnState($transactionDetails, $order, $payment);
        } catch (ApiException $apiException) {
            $this->apiExceptionToLog($apiException, $orderId);
        } catch (\Exception $e) {
            $this->logGeneralException($orderId, $e);
        }
    }

    /**
     * Updates order and payment statuses based on the transaction outcome.
     * @param $transactionDetails
     * @param $order
     * @param $payment
     * @return void
     */
    private function handleTransactionBasedOnState($transactionDetails, $order, $payment)
    {
        $txStateId = (int)$transactionDetails->txstateid;

        if (array_key_exists($txStateId, self::CANCELABLE_TXSTATEIDS)) {
            $payment->setAdditionalInformation('statusDetail', $transactionDetails->status);
            $payment->save();
            $order->cancel()->save();
            $this->logCancelledPayment($order->getEntityId());
        } elseif (array_key_exists($txStateId, self::CORRECT_TXSTATEIDS)) {
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus(Order::STATE_PROCESSING);
            $order->addStatusHistoryComment('Order automatically set to Processing based on successful transaction.');
            $payment->save();
            $order->save();
        }
    }

    /**
     * Check transaction fraud result and action based on result
     * @throws LocalizedException
     */
    public function checkFraud()
    {
        $transactions = $this->fraudModel->getShadowPaidPaymentTransactions();

        foreach ($transactions as $_transaction) {
            try {
                $transaction = $this->transactionRepository->get($_transaction["transaction_id"]);
                $logData = [];

                $payment = $this->orderPaymentRepository->get($transaction->getPaymentId());
                if ($payment === null) {
                    throw new LocalizedException(
                        __('Payment not found for this transaction.')
                    );
                }

                //process fraud information
                $logData = $this->fraudHelper->processFraudInformation($transaction, $payment);
            } catch (ApiException $apiException) {
                $logData["ERROR"] = $apiException->getUserMessage();
                $logData["Trace"] = $apiException->getTraceAsString();
            } catch (\Exception $e) {
                $logData["ERROR"] = $e->getMessage();
                $logData["Trace"] = $e->getTraceAsString();
            }

            //log
            $this->suiteLogger->sageLog(Logger::LOG_CRON, $logData, [__METHOD__, __LINE__]);
        }
        return $this;
    }

    /**
     * @param $orderId
     */
    private function logCancelledPayment($orderId)
    {
        $this->suiteLogger->sageLog(Logger::LOG_CRON, [
            "OrderId" => $orderId,
            "Result"  => "CANCELLED : No payment received."
        ], [__METHOD__, __LINE__]);
    }

    /**
     * @param $orderId
     */
    private function logErrorPaymentNotFound($orderId)
    {
        $this->suiteLogger->sageLog(Logger::LOG_CRON, [
            "OrderId" => $orderId,
            "Result"  => "ERROR : No payment found."
        ], [__METHOD__, __LINE__]);
    }

    /**
     * @param $orderId
     * @param $apiException
     */
    private function logApiException($orderId, $apiException)
    {
        $this->suiteLogger->sageLog(Logger::LOG_CRON, [
            "OrderId" => $orderId,
            "Result"  => $apiException->getUserMessage(),
            "Stack"   => $apiException->getTraceAsString()
        ], [__METHOD__, __LINE__]);
    }

    /**
     * @param $orderId
     * @param $e
     */
    private function logGeneralException($orderId, $e)
    {
        $this->suiteLogger->sageLog(Logger::LOG_CRON, [
            "OrderId" => $orderId,
            "Result"  => $e->getMessage(),
            "Trace"   => $e->getTraceAsString()
        ], [__METHOD__, __LINE__]);
    }

    /**
     * @param $orderId
     * @param $apiException
     */
    private function logTransactionNotFound($orderId, $apiException)
    {
        $this->suiteLogger->sageLog(Logger::LOG_CRON, [
            "OrderId" => $orderId,
            "Result"  => $apiException->getUserMessage() . " The transaction might still be in process"
        ], [__METHOD__, __LINE__]);
    }

    /**
     * @param $transactionId
     * @param Order $_order
     * @param Payment $payment
     * @return mixed
     * @throws ApiException
     */
    public function getTransactionDetails($transactionId, Order $_order, Payment $payment)
    {
        if ($transactionId != null) {
            $transactionDetails = $this->reportingApi->getTransactionDetailsByVpstxid(
                $transactionId,
                $_order->getStoreId()
            );
        } else {
            $vendorTxCode = $payment->getAdditionalInformation('vendorTxCode');
            $transactionDetails = $this->reportingApi->getTransactionDetailsByVendorTxCode(
                $vendorTxCode,
                $_order->getStoreId()
            );
        }
        return $transactionDetails;
    }

    /**
     * @param $apiException
     * @return bool
     */
    public function checkIfTransactionNotFound($apiException)
    {
        return $apiException->getCode() === self::TRANSACTION_NOT_FOUND;
    }

    /**
     * @param $apiException
     * @param int $orderId
     */
    private function apiExceptionToLog($apiException, int $orderId)
    {
        if ($this->checkIfTransactionNotFound($apiException)) {
            $this->logTransactionNotFound($orderId, $apiException);
        } else {
            $this->logApiException($orderId, $apiException);
        }
    }
}
