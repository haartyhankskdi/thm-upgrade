<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Order;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Sales\Model\OrderRepository;

class SyncFromApi implements ActionInterface
{

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\Reporting
     */
    private $reportingApi;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Fraud
     */
    private $fraudHelper;

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Data
     */
    private $suiteHelper;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository
     */
    private $transactionRepository;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var UrlInterface
     */
    private $backendUrl;
    /**
     * @var RedirectInterface
     */
    private $redirect;
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param \Ebizmarts\SagePaySuite\Model\Api\Reporting $reportingApi
     * @param OrderRepository $orderRepository
     * @param Logger $suiteLogger
     * @param \Ebizmarts\SagePaySuite\Helper\Fraud $fraudHelper
     * @param \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper
     * @param \Magento\Sales\Model\Order\Payment\Transaction\Repository $transactionRepository
     * @param RequestInterface $request
     * @param ManagerInterface $manager
     * @param UrlInterface $backendUrl
     * @param RedirectInterface $redirect
     * @param ResponseInterface $response
     */
    public function __construct(
        \Ebizmarts\SagePaySuite\Model\Api\Reporting $reportingApi,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger $suiteLogger,
        \Ebizmarts\SagePaySuite\Helper\Fraud $fraudHelper,
        \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper,
        \Magento\Sales\Model\Order\Payment\Transaction\Repository $transactionRepository,
        RequestInterface $request,
        ManagerInterface $manager,
        UrlInterface $backendUrl,
        RedirectInterface $redirect,
        ResponseInterface $response
    ) {
        $this->reportingApi          = $reportingApi;
        $this->orderRepository       = $orderRepository;
        $this->suiteLogger           = $suiteLogger;
        $this->fraudHelper           = $fraudHelper;
        $this->suiteHelper           = $suiteHelper;
        $this->transactionRepository = $transactionRepository;
        $this->request               = $request;
        $this->messageManager        = $manager;
        $this->backendUrl            = $backendUrl;
        $this->redirect              = $redirect;
        $this->response              = $response;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            //get order id
            $orderId = $this->getRequest()->getParam("order_id");

            if (!empty($orderId)) {
                $order = $this->orderRepository->get($orderId);
                $payment = $order->getPayment();
            } else {
                throw new ValidatorException(__('Unable to sync from API: Invalid order id.'));
            }

            $transactionIdDirty = $payment->getLastTransId();

            $transactionId = $this->suiteHelper->clearTransactionId($transactionIdDirty);

            if ($transactionId != null) {
                $transactionDetails = $this->reportingApi
                    ->getTransactionDetailsByVpstxid($transactionId, $order->getStoreId());
            } else {
                $vendorTxCode = $payment->getAdditionalInformation("vendorTxCode");
                $transactionDetails = $this->reportingApi
                    ->getTransactionDetailsByVendorTxCode($vendorTxCode, $order->getStoreId());
            }

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

            //update fraud status
            if (!empty($payment->getLastTransId())) {
                $transaction = $this->transactionRepository
                                ->getByTransactionId($payment->getLastTransId(), $payment->getId(), $order->getId());
                if ($this->shouldProcessFraudInformation($transaction, $payment)) {
                    $this->fraudHelper->processFraudInformation($transaction, $payment);
                }
            }

            $this->messageManager->addSuccessMessage(__('Successfully synced from Opayo\'s API'));
        } catch (ApiException $apiException) {
            $this->suiteLogger->sageLog(
                Logger::LOG_EXCEPTION,
                $apiException->getTraceAsString(),
                [__METHOD__, __LINE__]
            );
            $this->messageManager->addErrorMessage(__($this->cleanExceptionString($apiException)));
        } catch (\Exception $e) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
            $this->messageManager->addErrorMessage(__('Something went wrong: %1', $e->getMessage()));
        }

        $params = [
            '_nosid' => true,
            '_secure' => true
        ];

        if (!empty($order)) {
            $params['order_id'] = $order->getId();
            $params['_store'] = $order->getStoreId();
            $url = $this->backendUrl->getUrl('sales/order/view/', $params);
        } else {
            $url = $this->backendUrl->getUrl('sales/order/index/', $params);
        }
        $this->redirect->redirect($this->response, $url);
        return $this->response;
    }

    /**
     * @return bool
     */
    public function issetTransactionDetails($transactionDetails)
    {
        return isset($transactionDetails->vpstxid) && isset($transactionDetails->vendortxcode) &&
        isset($transactionDetails->status);
    }

    /**
     * This function replaces the < and > symbols, this is necessary for the exception to be showed correctly
     * to the customer at the backend.
     * @param $apiException
     * @return string|string[]
     */
    public function cleanExceptionString($apiException)
    {
        return str_replace(">", "", str_replace("<", "", $apiException->getUserMessage()));
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $transaction
     * @param $payment
     * @return bool
     */
    private function shouldProcessFraudInformation($transaction, $payment)
    {
        return $transaction !== false
        && ($this->isFraudNotChecked($transaction)
            || $this->additionalInformationNotCompleted($payment));
    }

    /**
     * @param $transaction
     * @return bool
     */
    private function isFraudNotChecked($transaction)
    {
        return (bool)$transaction->getSagepaysuiteFraudCheck() === false;
    }

    /**
     * @param $payment
     * @return bool
     */
    private function additionalInformationNotCompleted($payment)
    {
        $shouldUpdateFraud = false;

        if (!empty($payment->getAdditionalInformation("fraudrules"))) {
            $rules = $payment->getAdditionalInformation("fraudrules");
            foreach ($rules as $rule) {
                if (empty($rule)) {
                    $shouldUpdateFraud = true;
                    break;
                }
            }
        }

        return $shouldUpdateFraud;
    }
}
