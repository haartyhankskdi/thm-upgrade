<?php

namespace Ebizmarts\SagePaySuite\Cron;

use Ebizmarts\SagePaySuite\Api\Data\SyncOrderInterface;
use Ebizmarts\SagePaySuite\Api\Data\SyncOrderInterfaceFactory;
use Ebizmarts\SagePaySuite\Api\SyncFromApiModelInterface;
use Ebizmarts\SagePaySuite\Api\SyncFromApiModelInterfaceFactory;
use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class GetInfoSyncFromApi
{
    private const BATCH_LIMIT = 50;
    private const STATUSES = ['pending','processing', 'completed'];
    private const SYNC_ORDER = 'sync_order';
    private const ORDER_ID = 'order id';
    private const STATUS = 'status';
    private const RESULT = 'result';
    private const TRACE = 'trace';
    private const ALL_ATTRIBUTES = '*';
    private const TABLE_NAME = 'sagepaysuite_synced_orders';
    private const SAGE_PAY_SUITE = 'sagepaysuite';
    private const THREE_DSTATUS = 'threeDStatus';
    private const SECURITY_KEY = 'securityKey';
    private const STATUS_CODE = 'statusCode';
    private const STATUS_DETAIL = 'statusDetail';
    private const VENDOR_TX_CODE = 'vendorTxCode';
    private const IN_CONDITION = 'in';

    /** @var CollectionFactory $orderCollectionFactory */
    private $orderCollectionFactory;

    /** @var SyncFromApiModelInterfaceFactory $syncFromApiModelInterfaceFactory */
    private $syncFromApiModelInterfaceFactory;

    /** @var SyncOrderInterfaceFactory $syncOrderInterfaceFactory */
    private $syncOrderInterfaceFactory;

    /** @var Config $config */
    private $config;

    /** @var Data $suiteHelper */
    private $suiteHelper;

    /** @var Logger $suiteLogger */
    private $suiteLogger;

    /** @var Reporting $reportingApi */
    private $reportingApi;

    /** @var DeploymentConfig $deploymentConfig */
    protected $deploymentConfig;

    /** @var ResourceConnection $resource */
    private $resource;

    /**
     * GetInfoSyncFromApi constructor
     * @param CollectionFactory $collectionFactory
     * @param SyncOrderInterfaceFactory $syncOrderInterfaceFactory
     * @param SyncFromApiModelInterfaceFactory $syncFromApiModelInterfaceFactory
     * @param Config $config
     * @param Logger $suiteLogger
     * @param Data $suiteHelper
     * @param Reporting $reportingApi
     * @param DeploymentConfig $deploymentConfig
     * @param ResourceConnection $resource
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        SyncOrderInterfaceFactory $syncOrderInterfaceFactory,
        SyncFromApiModelInterfaceFactory $syncFromApiModelInterfaceFactory,
        Config $config,
        Data $suiteHelper,
        Logger $suiteLogger,
        Reporting $reportingApi,
        DeploymentConfig $deploymentConfig,
        ResourceConnection $resource
    ) {
        $this->orderCollectionFactory           = $collectionFactory;
        $this->syncOrderInterfaceFactory        = $syncOrderInterfaceFactory;
        $this->syncFromApiModelInterfaceFactory = $syncFromApiModelInterfaceFactory;
        $this->config                           = $config;
        $this->suiteHelper                      = $suiteHelper;
        $this->suiteLogger                      = $suiteLogger;
        $this->reportingApi                     = $reportingApi;
        $this->deploymentConfig                 = $deploymentConfig;
        $this->resource                         = $resource;
    }

    public function process()
    {
        if (!$this->isEnabled()) {
            return;
        }
        try {
            /** @var Collection $collection */
            $collection = $this->getCollectionToProcess();
            /** @var OrderInterface $order */
            foreach ($collection as $order) {
                /** @var OrderPaymentInterface $payment */
                $this->processOrder($order);
            }
        } catch (\Exception $exception) {
            $this->suiteLogger->sageLog(Logger::LOG_CRON_SYNC_FROM_API, [
                "Result"  => $exception->getMessage(),
                "Trace"   => $exception->getTraceAsString()
            ], [__METHOD__, __LINE__]);
        }
    }

    /**
     * @return bool
     */
    private function isEnabled()
    {
        return $this->config->getGlobalValue(self::SYNC_ORDER) == "1";
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
     * @param $tableName
     * @param $conn
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function getTableName(
        $tableName,
        $conn = ResourceConnection::DEFAULT_CONNECTION
    ) {
        $dbName = $this->deploymentConfig->get("db/connection/$conn/dbname");
        return $dbName . '.' . $this->resource->getTableName($tableName, $conn);
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function getCollectionToProcess()
    {
        $collection = $this->orderCollectionFactory
            ->create()
            ->addAttributeToSelect(self::ALL_ATTRIBUTES)
            ->addAttributeToFilter(self::STATUS, [self::IN_CONDITION => self::STATUSES]);
        $collection->getSelect()->joinLeft(
            ['so' => $this->getTableName(self::TABLE_NAME)],
            "so.order_id = main_table.entity_id",
            ['so.*']
        );
        $attempts = $this->config->getSyncAttempts();
        $collection->getSelect()->where("so.sync_attempts < $attempts OR so.sync_attempts IS NULL");
        $collection->getSelect()->limit(self::BATCH_LIMIT);
        return $collection;
    }

    /**
     * @param $payment
     * @param $storeId
     * @return mixed|object
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    private function getTransactionDetails($payment, $storeId)
    {
        $transactionIdDirty = $payment->getLastTransId();
        $transactionId = $this->suiteHelper->clearTransactionId($transactionIdDirty);
        if ($transactionId != null) {
            $transactionDetails = $this->reportingApi
                ->getTransactionDetailsByVpstxid($transactionId, $storeId);
        } else {
            $vendorTxCode = $payment->getAdditionalInformation(self::VENDOR_TX_CODE);
            $transactionDetails = $this->reportingApi
                ->getTransactionDetailsByVendorTxCode($vendorTxCode, $storeId);
        }
        return $transactionDetails;
    }

    private function processOrder($order)
    {
        $payment = $order->getPayment();
        if ($this->isSagePayment($payment->getMethod())) {
            try {
                $orderId = $order->getId();
                $this->suiteLogger->sageLog(Logger::LOG_CRON_SYNC_FROM_API, [
                    "Order id"  => $orderId,
                    "Status"   => $payment->getAdditionalInformation(self::STATUS_DETAIL)
                ], [__METHOD__, __LINE__]);
                $storeId = $order->getStoreId();
                $transactionDetails = $this->getTransactionDetails($payment, $storeId);
                if ($this->issetTransactionDetails($transactionDetails)) {
                    $this->syncOrder($transactionDetails, $payment);
                    $this->updateSagepaysuiteSyncedOrders($payment, $orderId);
                }
            } catch (\Exception $exception) {
                $this->suiteLogger->sageLog(Logger::LOG_CRON_SYNC_FROM_API, [
                    "Result"  => $exception->getMessage(),
                    "Trace"   => $exception->getTraceAsString()
                ], [__METHOD__, __LINE__]);
            }
        }
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param mixed $orderId
     * @return void
     */
    private function updateSagepaysuiteSyncedOrders($payment, $orderId)
    {
        /** @var SyncOrderInterface $syncOrderInterface */
        $syncOrderInterface = $this->syncOrderInterfaceFactory->create();
        $syncOrderInterface->setOrderId($orderId);
        $syncOrderInterface->setStatusCode($payment->getAdditionalInformation(self::STATUS_CODE));
        $syncOrderInterface->setStatusDetail($payment->getAdditionalInformation(self::STATUS_DETAIL));
        /** @var SyncFromApiModelInterface $syncFromApiModelInterface */
        $syncFromApiModelInterface = $this->syncFromApiModelInterfaceFactory->create();
        /** @var SyncOrderInterface $order */
        $order = $syncFromApiModelInterface->getByOrderId($syncOrderInterface->getOrderId());
        if ($order != null) {
            $syncFromApiModelInterface->updateTable($order);
        } else {
            $syncFromApiModelInterface->saveSyncedOrder($syncOrderInterface);
        }
    }

    /**
     * @param mixed $transactionDetails
     * @param OrderPaymentInterface $payment
     * @return void
     */
    private function syncOrder($transactionDetails, $payment)
    {
        $payment->setLastTransId((string)$transactionDetails->vpstxid);
        $payment->setAdditionalInformation('vendorTxCode', (string)$transactionDetails->vendortxcode);
        $payment->setAdditionalInformation(self::STATUS_DETAIL, (string)$transactionDetails->status);
        if (isset($transactionDetails->securitykey)) {
            $payment->setAdditionalInformation(self::SECURITY_KEY, (string)$transactionDetails->securitykey);
        }
        if (isset($transactionDetails->threedresult)) {
            $payment->setAdditionalInformation(self::THREE_DSTATUS, (string)$transactionDetails->threedresult);
        }
        $payment->save();
    }

    /**
     * @param string $paymentMethod
     * @return bool
     */
    private function isSagePayment($paymentMethod)
    {
        return strpos($paymentMethod, self::SAGE_PAY_SUITE) !== false;
    }
}
