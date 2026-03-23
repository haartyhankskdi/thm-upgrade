<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Api;

use Ebizmarts\SagePaySuite\Model\Payment\Deferred as SagePaymentDeferred;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\Api\Deferred;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Sage Pay Shared API
 */
class Shared implements PaymentOperationsInterface
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\ApiExceptionFactory
     */
    private $apiExceptionFactory;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config
     */
    private $config;

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Data
     */
    private $suiteHelper;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\Reporting
     */
    private $reportingApi;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\HttpTextFactory  */
    private $httpTextFactory;

    private $requestHelper;
    /**
     * @var Deferred
     */
    private $deferred;

    /** @var SagePaymentDeferred $sagePaymentDeferred */
    private $sagePaymentDeferred;

    /**
     * Shared constructor.
     * @param HttpTextFactory $httpTextFactory
     * @param ApiExceptionFactory $apiExceptionFactory
     * @param Config $config
     * @param \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper
     * @param Reporting $reportingApi
     * @param Logger $suiteLogger
     * @param Deferred $deferred
     * @param SagePaymentDeferred $sagePaymentDeferred
     */
    public function __construct(
        \Ebizmarts\SagePaySuite\Model\Api\HttpTextFactory $httpTextFactory,
        \Ebizmarts\SagePaySuite\Model\Api\ApiExceptionFactory $apiExceptionFactory,
        \Ebizmarts\SagePaySuite\Model\Config $config,
        \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper,
        \Ebizmarts\SagePaySuite\Model\Api\Reporting $reportingApi,
        Logger $suiteLogger,
        \Ebizmarts\SagePaySuite\Helper\Request $requestHelper,
        Deferred $deferred,
        SagePaymentDeferred $sagePaymentDeferred
    ) {
        $this->config              = $config;
        $this->apiExceptionFactory = $apiExceptionFactory;
        $this->suiteLogger         = $suiteLogger;
        $this->suiteHelper         = $suiteHelper;
        $this->reportingApi        = $reportingApi;
        $this->httpTextFactory     = $httpTextFactory;
        $this->requestHelper       = $requestHelper;
        $this->deferred            = $deferred;
        $this->sagePaymentDeferred = $sagePaymentDeferred;
    }

    /**
     * @param object $transactionDetails
     * @return array
     */
    public function voidTransaction($transactionDetails)
    {
        $data = [];
        $data['VPSProtocol'] = $this->config->getVPSProtocol();
        $data['TxType'] = Config::ACTION_VOID;
        $data['Vendor'] = $this->config->getVendorname();
        $data['VendorTxCode'] = $this->suiteHelper->generateVendorTxCode();
        $data['VPSTxId'] = (string)$transactionDetails->vpstxid;
        $data['SecurityKey'] = (string)$transactionDetails->securitykey;
        $data['TxAuthNo'] = (string)$transactionDetails->vpsauthcode;

        return $this->executeRequest(Config::ACTION_VOID, $data);
    }

    /**
     * @param object $transactionDetails
     * @return array
     */
    public function cancelAuthenticatedTransaction($transactionDetails)
    {
        $data = [];
        $data['VPSProtocol'] = $this->config->getVPSProtocol();
        $data['TxType'] = Config::ACTION_CANCEL;
        $data['Vendor'] = $this->config->getVendorname();
        $data['VendorTxCode'] = (string)$transactionDetails->vendortxcode;
        ;
        $data['VPSTxId'] = (string)$transactionDetails->vpstxid;
        $data['SecurityKey'] = (string)$transactionDetails->securitykey;

        return $this->executeRequest(Config::ACTION_CANCEL, $data);
    }

    public function refundTransaction($vpstxid, $amount, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $transaction = $this->reportingApi->getTransactionDetailsByVpstxid($vpstxid, $order->getStoreId());

        $data['VPSProtocol']         = $this->config->getVPSProtocol();
        $data['TxType']              = Config::ACTION_REFUND;
        $data['Vendor']              = $this->config->getVendorname();
        $data['VendorTxCode']        = $this->suiteHelper->generateVendorTxCode(
            $order->getIncrementId(),
            Config::ACTION_REFUND
        );
        $data['Amount']              = number_format($amount, 2, '.', '');
        $data['Currency']            = (string)$transaction->currency;
        $data['Description']         = "Refund issued from magento.";
        $data['RelatedVPSTxId']      = (string)$transaction->vpstxid;
        $data['RelatedVendorTxCode'] = (string)$transaction->vendortxcode;
        $data['RelatedSecurityKey']  = (string)$transaction->securitykey;
        $data['RelatedTxAuthNo']     = (string)$transaction->vpsauthcode;

        return $this->executeRequest(Config::ACTION_REFUND, $data);
    }

    /**
     * @param object $transactionDetails
     * @return array
     */
    public function abortDeferredTransaction($transactionDetails)
    {
        $data = [];
        $data['VPSProtocol']  = $this->config->getVPSProtocol();
        $data['TxType']       = Config::ACTION_ABORT;
        $data['ReferrerID']   = $this->requestHelper->getReferrerId();
        $data['Vendor']       = $this->config->getVendorname();
        $data['VendorTxCode'] = (string)$transactionDetails->vendortxcode;
        $data['VPSTxId']      = (string)$transactionDetails->vpstxid;
        $data['SecurityKey']  = (string)$transactionDetails->securitykey;
        $data['TxAuthNo']     = (string)$transactionDetails->vpsauthcode;

        return $this->executeRequest(Config::ACTION_ABORT, $data);
    }

    /**
     * @throws ApiException
     * @throws LocalizedException
     */
    public function captureDeferredTransaction($vpsTxId, $amount, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->deferred->getReleasedAmount($order);
        $shouldAssociateTransaction = $this->sagePaymentDeferred->shouldAssociateTransaction($order);
        $vpsTxId = $this->suiteHelper->clearTransactionId($vpsTxId);

        $transaction = $this->reportingApi->getTransactionDetailsByVpstxid($vpsTxId, $order->getStoreId());
        $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $transaction, [__METHOD__, __LINE__]);

        $result = null;

        $txStateId = (int)$transaction->txstateid;
        if ($txStateId == PaymentOperationsInterface::DEFERRED_AWAITING_RELEASE) {
            $result = $this->releaseTransaction($vpsTxId, $amount, $order);
            $order->getPayment()->setTotalReleased(0)->save();
        } elseif ($txStateId == PaymentOperationsInterface::SUCCESSFULLY_AUTHORISED) {
            $total = $this->deferred->getRepeatAmount($amount, $order);
            if ($total > 0) {
                $data = [];
                $data['VendorTxCode'] = $this->suiteHelper->generateVendorTxCode("", Config::ACTION_REPEAT);
                $data['Description'] = "REPEAT deferred transaction from Magento.";
                $data['ReferrerID'] = $this->requestHelper->getReferrerId();
                $data['Currency'] = (string)$transaction->currency;
                $data['Amount'] = $total;
                $result = $this->repeatTransaction($vpsTxId, $data, $order, Config::ACTION_REPEAT);
                if ($shouldAssociateTransaction) {
                    $this->sagePaymentDeferred->updatePaymentAdditionalInformation($order, $result, $amount, $total);
                }
            }
        } elseif ($txStateId === PaymentOperationsInterface::SUCCESSFULLY_DEFERRED_ABORTED_BY_VENDOR) {
            throw new LocalizedException(__($this->getErrorMessage()));
        }

        return $result;
    }

    public function releaseTransaction($vpstxid, $amount, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $transaction = $this->reportingApi->getTransactionDetailsByVpstxid($vpstxid, $order->getStoreId());

        $data['VPSProtocol']   = $this->config->getVPSProtocol();
        $data['TxType']        = Config::ACTION_RELEASE;
        $data['Vendor']        = $this->config->getVendorname();
        $data['VendorTxCode']  = (string)$transaction->vendortxcode;
        $data['VPSTxId']       = (string)$transaction->vpstxid;
        $data['SecurityKey']   = (string)$transaction->securitykey;
        $data['TxAuthNo']      = (string)$transaction->vpsauthcode;
        $data['ReleaseAmount'] = number_format($amount, 2, '.', '');

        return $this->executeRequest(Config::ACTION_RELEASE, $data);
    }

    public function authorizeTransaction($vpstxid, $amount, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $transaction = $this->reportingApi->getTransactionDetailsByVpstxid($vpstxid, $order->getStoreId());

        $data['VPSProtocol']         = $this->config->getVPSProtocol();
        $data['TxType']              = \Ebizmarts\SagePaySuite\Model\Config::ACTION_AUTHORISE;
        $data['Vendor']              = $this->config->getVendorname();
        $data['VendorTxCode']        = $this->suiteHelper->generateVendorTxCode(
            $order->getIncrementId(),
            Config::ACTION_AUTHORISE
        );
        $data['Amount']              = number_format($amount, 2, '.', '');
        $data['Description']         = "Authorise transaction from Magento";
        $data['RelatedVPSTxId']      = (string)$transaction->vpstxid;
        $data['RelatedVendorTxCode'] = (string)$transaction->vendortxcode;
        $data['RelatedSecurityKey']  = (string)$transaction->securitykey;
        $data['RelatedTxAuthNo']     = (string)$transaction->vpsauthcode;

        return $this->executeRequest(Config::ACTION_AUTHORISE, $data);
    }

    public function repeatTransaction(
        $vpstxid,
        $quote_data,
        \Magento\Sales\Api\Data\OrderInterface $order,
        $paymentAction = Config::ACTION_REPEAT
    ) {
        $transaction = $this->reportingApi->getTransactionDetailsByVpstxid($vpstxid, $order->getStoreId());

        $data['VPSProtocol'] = $this->config->getVPSProtocol();
        $data['TxType']      = $paymentAction;
        $data['Vendor']      = $this->config->getVendorname();

        //populate quote data
        $data = array_merge($data, $quote_data);

        $data['Description']         = "Repeat transaction from Magento";
        $data['RelatedVPSTxId']      = (string)$transaction->vpstxid;
        $data['RelatedVendorTxCode'] = (string)$transaction->vendortxcode;
        $data['RelatedSecurityKey']  = (string)$transaction->securitykey;
        $data['RelatedTxAuthNo']     = (string)$transaction->vpsauthcode;
        $data['COFUsage']            = 'SUBSEQUENT';
        $data['InitiatedType']       = 'MIT';
        $data['MITType']             = 'UNSCHEDULED';

        return $this->executeRequest($paymentAction, $data);
    }

    /**
     * Executes curl request
     *
     * @param $action
     * @param $data
     * @return array
     */
    private function executeRequest($action, $data)
    {
        $url = $this->config->getServiceUrl($action);

        /** @var \Ebizmarts\SagePaySuite\Model\Api\HttpText $rest */
        $rest = $this->httpTextFactory->create();
        $body = $rest->arrayToQueryParams($data);
        $rest->setUrl($url);
        $response = $rest->executePost($body);

        //parse response
        if ($response->getStatus() == 200) {
            $responseData = $rest->rawResponseToArray();
        } else {
            $responseData = [];
            $this->suiteLogger->sageLog(
                Logger::LOG_REQUEST,
                "INVALID RESPONSE FROM SAGE PAY: " . $response->getResponseCode(),
                [__METHOD__, __LINE__]
            );
        }

        $response = [
            "status" => $response->getStatus(),
            "data"   => $responseData
        ];

        $apiResponse = $this->handleApiErrors($response);

        //log response
        $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $apiResponse, [__METHOD__, __LINE__]);

        return $apiResponse;
    }

    /**
     * @param $response
     * @return array
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    private function handleApiErrors($response)
    {
        $exceptionPhrase = "Invalid response from Elavon API.";
        $exceptionCode = 0;
        $validResponse = false;

        if (!empty($response) && isset($response["data"])) {
            if (isset($response["data"]["Status"]) && $response["data"]["Status"] == 'OK') {
                //this is a successfull response
                return $response;
            } else {
                //there was an error
                if (isset($response["data"]["StatusDetail"])) {
                    $detail = explode(":", $response["data"]["StatusDetail"]);
                    $exceptionCode = trim($detail[0]);
                    $exceptionPhrase = trim($detail[1]);
                    $validResponse = true;
                }
            }
        }

        if (!$validResponse) {
            $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $response, [__METHOD__, __LINE__]);
        }
        /** @var \Ebizmarts\SagePaySuite\Model\Api\ApiException $exception */
        $exception = $this->apiExceptionFactory->create([
            'phrase' => __($exceptionPhrase),
            'code' => $exceptionCode
        ]);
        throw $exception;
    }

    /**
     * @return string
     */
    private function getErrorMessage()
    {
        return PaymentOperationsInterface::ERROR_SUCCESSFULLY_DEFERRED_ABORTED_BY_VENDOR;
    }
}
