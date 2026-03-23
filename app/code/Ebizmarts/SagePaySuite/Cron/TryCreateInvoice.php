<?php

namespace Ebizmarts\SagePaySuite\Cron;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Helper\Checkout;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\FilterFactory;
use Magento\Framework\Api\Filter;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory;
use Ebizmarts\SagePaySuite\Model\Config\ClosedForAction;
use Magento\Sales\Api\TransactionRepositoryInterfaceFactory;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\Data\TransactionInterfaceFactory;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Framework\Exception\AlreadyExistsException;

class TryCreateInvoice
{
    public const PAYMENT_ACTION = 'paymentAction';
    public const IS_ENABLED_CONFIRMATION_NOTIFICATION_EMAIL = "1";
    private const PROPERTY_STATUS = 'status';
    public const PROPERTY_STATUS_DOES_NOT_EXIST = "Property status does not exist for this transaction";
    private const PAYMENT_ACTION_IS_NOT_CAPTURE = "Payment action not is capture for this order.";
    private const TRANSACTION_DETAILS_ARE_NOT_SET = "Transaction details are not set for this order.";
    public const SUCCESSFULLY_AUTHORISED_STATUS = 'Successfully authorised transaction.';
    private const THREE_DSTATUS = 'threeDStatus';
    private const SECURITY_KEY = 'securityKey';
    public const STATUS_DETAIL = 'statusDetail';
    public const VENDOR_TX_CODE = 'vendorTxCode';
    private const DATE_FORMAT = 'Y-m-d H:i:s';
    public const CREATED_AT_FIELD = 'created_at';
    public const BETWEEN_CONDITION = 'between';
    private const FROM_DATE = 'from';
    private const TO_DATE = 'to';
    private const IS_DATE = 'date';
    private const CURRENT_TIME_MINUS_TWO_HOURS = '-2 hour';
    public const PENDING_PAYMENT_VALUE = 'pending_payment';
    public const EQUAL_CONDITION = 'eq';
    public const STATE_FIELD = 'state';

    /** @var Logger $suiteLogger */
    private $suiteLogger;

    /** @var Reporting $reportingApi */
    private $reportingApi;

    /** @var Checkout $checkoutHelper */
    private $checkoutHelper;

    /** @var Config $config */
    private $config;

    /** @var InvoiceSender $invoiceEmailSender */
    private $invoiceEmailSender;

    /** @var TransactionInterfaceFactory $transactionFactory */
    private $transactionFactory;

    /** @var ClosedForActionFactory $closedForActionFactory */
    private $closedForActionFactory;

    /** @var TransactionRepositoryInterfaceFactory $transactionRepositoryInterfaceFactory */
    private $transactionRepositoryInterfaceFactory;

    /** @var OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory */
    private $orderRepositoryInterfaceFactory;

    /** @var OrderPaymentRepositoryInterfaceFactory $orderPaymentRepositoryInterfaceFactory */
    private $orderPaymentRepositoryInterfaceFactory;

    /** @var SearchCriteriaInterfaceFactory $searchCriteriaInterfaceFactory */
    private $searchCriteriaInterfaceFactory;

    /** @var FilterFactory $filterFactory */
    private $filterFactory;

    /** @var FilterGroupBuilder $filterGroupBuilder */
    private $filterGroupBuilder;

    private $ordersProcessed = 0;

    private $ordersNotProcessed = 0;

    /**
     * TryCreateInvoice
     *
     * @param Logger $suiteLogger
     * @param Reporting $reportingApi
     * @param Checkout $checkoutHelper
     * @param Config $config
     * @param InvoiceSender $invoiceEmailSender
     * @param TransactionInterfaceFactory $transactionFactory
     * @param ClosedForActionFactory $closedForActionFactory
     * @param TransactionRepositoryInterfaceFactory $transactionRepositoryInterfaceFactory
     * @param OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory
     * @param OrderPaymentRepositoryInterfaceFactory $orderPaymentRepositoryInterfaceFactory
     * @param SearchCriteriaInterfaceFactory $searchCriteriaInterfaceFactory
     * @param FilterFactory $filterFactory
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        Logger $suiteLogger,
        Reporting $reportingApi,
        Checkout $checkoutHelper,
        Config $config,
        InvoiceSender $invoiceEmailSender,
        TransactionInterfaceFactory $transactionFactory,
        ClosedForActionFactory $closedForActionFactory,
        TransactionRepositoryInterfaceFactory $transactionRepositoryInterfaceFactory,
        OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory,
        OrderPaymentRepositoryInterfaceFactory $orderPaymentRepositoryInterfaceFactory,
        SearchCriteriaInterfaceFactory $searchCriteriaInterfaceFactory,
        FilterFactory $filterFactory,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->suiteLogger = $suiteLogger;
        $this->reportingApi = $reportingApi;
        $this->checkoutHelper = $checkoutHelper;
        $this->config = $config;
        $this->invoiceEmailSender = $invoiceEmailSender;
        $this->transactionFactory = $transactionFactory;
        $this->closedForActionFactory = $closedForActionFactory;
        $this->transactionRepositoryInterfaceFactory = $transactionRepositoryInterfaceFactory;
        $this->orderRepositoryInterfaceFactory = $orderRepositoryInterfaceFactory;
        $this->orderPaymentRepositoryInterfaceFactory = $orderPaymentRepositoryInterfaceFactory;
        $this->searchCriteriaInterfaceFactory = $searchCriteriaInterfaceFactory;
        $this->filterFactory = $filterFactory;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * @return void
     */
    public function process()
    {
        try {
            /** @var SearchCriteriaInterface $searchCriteria */
            $searchCriteria = $this->buildSearchCriteria();
            /** @var OrderRepositoryInterface $orderRepositoryInterface */
            $orderRepositoryInterface = $this->orderRepositoryInterfaceFactory->create();
            /** @var OrderSearchResultInterface $ordersToProcess */
            $ordersToProcess = $orderRepositoryInterface->getList($searchCriteria);
            /** @var OrderInterface[] $items */
            $items = $ordersToProcess->getItems();
            $this->logStartCronjob($items);
            /** @var OrderInterface $item */
            foreach ($items as $item) {
                $this->tryToCreateInvoice($item);
            }
        } catch (\Exception $exception) {
            $this->suiteLogger->sageLog(Logger::LOG_CRON_TRY_CREATE_INVOICE, [
                "Result"  => $exception->getMessage(),
                "Trace"   => $exception->getTraceAsString()
            ], [__METHOD__, __LINE__]);
        }
        $this->logEndCronjob($this->ordersProcessed, $this->ordersNotProcessed);
    }

    /**
     * @return SearchCriteriaInterface
     */
    private function buildSearchCriteria()
    {
        /** @var SearchCriteriaInterface $searchCriteria */
        $searchCriteria = $this->searchCriteriaInterfaceFactory->create();
        $filterState = $this->addAttributeToFilter(
            self::STATE_FIELD,
            self::EQUAL_CONDITION,
            self::PENDING_PAYMENT_VALUE
        );
        $filterGroupState = $this->filterGroupBuilder
            ->addFilter($filterState)
            ->create();

        $fromDate = date(self::DATE_FORMAT, strtotime(self::CURRENT_TIME_MINUS_TWO_HOURS));
        $toDate = date(self::DATE_FORMAT, strtotime(gmdate(self::DATE_FORMAT)));
        $filterDate = $this->addAttributeToFilter(
            self::CREATED_AT_FIELD,
            self::BETWEEN_CONDITION,
            [
                self::FROM_DATE => $fromDate,
                self::TO_DATE => $toDate,
                self::IS_DATE => true,
            ]
        );
        $filterGroupDate = $this->filterGroupBuilder
            ->addFilter($filterDate)
            ->create();

        $searchCriteria->setFilterGroups([$filterGroupState, $filterGroupDate]);

        return $searchCriteria;
    }

    /**
     * @param $field
     * @param $condition
     * @param $value
     * @return Filter
     */
    private function addAttributeToFilter($field, $condition, $value)
    {
        /** @var Filter $filter */
        $filter = $this->filterFactory->create();
        $filter->setField($field);
        $filter->setConditionType($condition);
        $filter->setValue($value);

        return $filter;
    }

    /**
     * @param $transactionDetails
     * @return bool
     */
    private function issetTransactionDetails($transactionDetails)
    {
        return (isset($transactionDetails->vpstxid)
            && isset($transactionDetails->vendortxcode)
            && isset($transactionDetails->status)
        );
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param OrderInterface $order
     * @param mixed $transactionDetails
     * @return void
     */
    private function createInvoiceForSuccessPayment($payment, $order, $transactionDetails)
    {
        $sagePayPaymentAction = $transactionDetails->transactiontype;
        $payment->getMethodInstance()->markAsInitialized();
        $order->place()->save();

        $this->checkoutHelper->sendOrderEmail($order);
        $this->sendInvoiceNotification($order);

        $this->createTransaction($payment, $order, $transactionDetails, $sagePayPaymentAction);
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param OrderInterface $order
     * @param mixed $transactionDetails
     * @param string $sagePayPaymentAction
     * @return void
     */
    private function createTransaction($payment, $order, $transactionDetails, $sagePayPaymentAction)
    {
        /** @var ClosedForAction $actionClosed */
        $actionClosed = $this->closedForActionFactory->create([self::PAYMENT_ACTION => $sagePayPaymentAction]);
        list($action, $closed) = $actionClosed->getActionClosedForPaymentAction();

        /** @var TransactionInterface $transaction */
        $transaction = $this->transactionFactory->create();
        $transaction->setOrderPaymentObject($payment);
        $transaction->setTxnId($transactionDetails->vpstxid);
        $transaction->setOrderId($order->getEntityId());
        $transaction->setTxnType($action);
        $transaction->setPaymentId($payment->getId());
        $transaction->setIsClosed($closed);
        /** @var TransactionRepositoryInterface $transactionRepository */
        $transactionRepository = $this->transactionRepositoryInterfaceFactory->create();
        $transactionRepository->save($transaction);
    }

    /**
     * @param mixed $transactionDetails
     * @return bool
     */
    private function shouldTryToCreateInvoice($transactionDetails)
    {
        return ($this->propertyExists($transactionDetails, self::PROPERTY_STATUS)
            && $transactionDetails->status === self::SUCCESSFULLY_AUTHORISED_STATUS
        );
    }

    /**
     * @param mixed $transactionDetails
     * @param string $property
     * @return bool
     */
    private function propertyExists($transactionDetails, $property)
    {
        return property_exists($transactionDetails, $property);
    }

    /**
     * @param OrderInterface $order
     * @return void
     * @throws \Exception
     */
    private function sendInvoiceNotification($order)
    {
        if ($this->invoiceConfirmationIsEnable()) {
            $invoices = $order->getInvoiceCollection();
            if ($invoices->count() > 0) {
                $this->invoiceEmailSender->send($invoices->getFirstItem());
            }
        }
    }

    /**
     * @return bool
     */
    private function invoiceConfirmationIsEnable()
    {
        return (string)$this->config->getInvoiceConfirmationNotification() ===
            self::IS_ENABLED_CONFIRMATION_NOTIFICATION_EMAIL;
    }

    /**
     * @param OrderPaymentInterface $orderPayment
     * @param string[] $orderPaymentAdditionalInformation
     * @return bool
     */
    private function isPiPaymentAndActionPayment($orderPayment, $orderPaymentAdditionalInformation)
    {
        return ($orderPayment->getMethod() === Config::METHOD_PI &&
            isset($orderPaymentAdditionalInformation[self::PAYMENT_ACTION])
            && $orderPaymentAdditionalInformation[self::PAYMENT_ACTION] === Config::ACTION_PAYMENT_PI
        );
    }

    /**
     * @param OrderInterface[] $items
     * @return void
     */
    private function logStartCronjob($items)
    {
        $message = "\n";
        $message .= '---------- ';
        $message .= "Starting cronjob try create invoice, amount of orders to process on the last hour " .
            count($items);
        $message .= ' ----------';
        $this->suiteLogger->sageLog(
            Logger::LOG_CRON_TRY_CREATE_INVOICE,
            $message,
            [__METHOD__, __LINE__]
        );
    }

    /**
     * @param int $ordersProcessed
     * @param int $ordersNotProcessed
     * @return void
     */
    private function logEndCronjob($ordersProcessed, $ordersNotProcessed)
    {
        $message = "\n";
        $message .= '---------- ';
        $message .= "End cronjob try create invoice, amount of orders processed " . $ordersProcessed .
            " amount of orders not processed " . $ordersNotProcessed;
        $message .= ' ----------';
        $this->suiteLogger->sageLog(
            Logger::LOG_CRON_TRY_CREATE_INVOICE,
            $message,
            [__METHOD__, __LINE__]
        );
    }

    /**
     * @param string $orderIncrementId
     * @param string $paymentMethod
     * @param string $paymentAction
     * @param string $statusTransaction
     * @return void
     */
    private function logOrderProcessed(
        $orderIncrementId,
        $paymentMethod,
        $paymentAction,
        $statusTransaction = null
    ) {
        $message = "\n";
        $message .= '---------- ';
        $message .= "Processed order with increment id: " . $orderIncrementId . " payment method: " . $paymentMethod .
            " payment action: " . $paymentAction . " and status transaction: " . $statusTransaction;
        $message .= ' ----------';
        $this->suiteLogger->sageLog(
            Logger::LOG_CRON_TRY_CREATE_INVOICE,
            $message,
            [__METHOD__, __LINE__]
        );
    }

    /**
     * @param OrderInterface $item
     * @return void
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    private function tryToCreateInvoice($item)
    {
        /** @var OrderPaymentInterface $orderPayment */
        $orderPayment = $item->getPayment();
        /** @var string[] $orderPaymentAdditionalInformation */
        $orderPaymentAdditionalInformation = $orderPayment->getAdditionalInformation();
        if ($this->isPiPaymentAndActionPayment($orderPayment, $orderPaymentAdditionalInformation)) {
            $vendorTxCode = $orderPaymentAdditionalInformation[self::VENDOR_TX_CODE];
            $transactionDetails = $this->reportingApi->getTransactionDetailsByVendorTxCode($vendorTxCode);
            if ($this->issetTransactionDetails($transactionDetails)) {
                $orderPayment->setLastTransId((string)$transactionDetails->vpstxid);
                $orderPayment->setAdditionalInformation(
                    self::VENDOR_TX_CODE,
                    (string)$transactionDetails->vendortxcode
                );
                $orderPayment->setAdditionalInformation(
                    self::STATUS_DETAIL,
                    (string)$transactionDetails->status
                );

                if (isset($transactionDetails->securitykey)) {
                    $orderPayment->setAdditionalInformation(
                        self::SECURITY_KEY,
                        (string)$transactionDetails->securitykey
                    );
                }

                if (isset($transactionDetails->threedresult)) {
                    $orderPayment->setAdditionalInformation(
                        self::THREE_DSTATUS,
                        (string)$transactionDetails->threedresult
                    );
                }

                /** @var OrderPaymentRepositoryInterface $orderPaymentRepositoryInterface */
                $orderPaymentRepositoryInterface = $this->orderPaymentRepositoryInterfaceFactory->create();
                $orderPayment = $orderPaymentRepositoryInterface->save($orderPayment);

                $this->suiteLogger->sageLog(
                    Logger::LOG_CRON_TRY_CREATE_INVOICE,
                    $orderPayment->getData(),
                    [__METHOD__, __LINE__]
                );

                if ($this->shouldTryToCreateInvoice($transactionDetails)) {
                    try {
                        $this->createInvoiceForSuccessPayment($orderPayment, $item, $transactionDetails);
                        $this->logOrderProcessed(
                            $item->getIncrementId(),
                            $orderPayment->getMethod(),
                            $orderPaymentAdditionalInformation[self::PAYMENT_ACTION],
                            $transactionDetails->status
                        );
                        $this->ordersProcessed++;
                    } catch (AlreadyExistsException $exception) {
                        $transactionStatus = $this->propertyExists($transactionDetails, self::PROPERTY_STATUS)
                            ? $transactionDetails->status
                            : self::PROPERTY_STATUS_DOES_NOT_EXIST ;
                        $this->logOrderProcessed(
                            $item->getIncrementId(),
                            $orderPayment->getMethod(),
                            $orderPaymentAdditionalInformation[self::PAYMENT_ACTION],
                            $transactionStatus
                        );
                        $this->suiteLogger->sageLog(Logger::LOG_CRON_TRY_CREATE_INVOICE, [
                            "Result"  => $exception->getMessage(),
                            "Trace"   => $exception->getTraceAsString()
                        ], [__METHOD__, __LINE__]);
                        $this->ordersNotProcessed++;
                    }
                } else {
                    $transactionStatus = $this->propertyExists($transactionDetails, self::PROPERTY_STATUS)
                        ? $transactionDetails->status
                        : self::PROPERTY_STATUS_DOES_NOT_EXIST ;
                    $this->logOrderProcessed(
                        $item->getIncrementId(),
                        $orderPayment->getMethod(),
                        $orderPaymentAdditionalInformation[self::PAYMENT_ACTION],
                        $transactionStatus
                    );
                    $this->ordersNotProcessed++;
                }
            } else {
                $this->logOrderProcessed(
                    $item->getIncrementId(),
                    $orderPayment->getMethod(),
                    $orderPaymentAdditionalInformation[self::PAYMENT_ACTION],
                    self::TRANSACTION_DETAILS_ARE_NOT_SET
                );
                $this->ordersNotProcessed++;
            }
        } else {
            $this->logOrderProcessed(
                $item->getIncrementId(),
                $orderPayment->getMethod(),
                $orderPaymentAdditionalInformation[self::PAYMENT_ACTION],
                self::PAYMENT_ACTION_IS_NOT_CAPTURE
            );
            $this->ordersNotProcessed++;
        }
    }
}
