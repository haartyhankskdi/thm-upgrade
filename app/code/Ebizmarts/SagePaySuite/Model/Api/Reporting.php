<?php

/**
 * Copyright © 2025 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 * @see https://developer.elavon.com/products/en-uk/opayo-reporting-api/v1/opayo-reporting-api
 */

namespace Ebizmarts\SagePaySuite\Model\Api;

use Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterfaceFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenRuleInterfaceFactory;
use Ebizmarts\SagePaySuite\Helper\Fraud;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\ObjectManager\ObjectManager;
use \Ebizmarts\SagePaySuite\Model\Api\ReportingSignature\ReportingSignatureStrategyContextFactory;

/**
 * Sage Pay Reporting API parent class
 */
class Reporting
{
    private const DEFAULT_SUBNET_MASK = "255.255.255.255";

    /**
     * @var ApiExceptionFactory
     */
    private $apiExceptionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $suiteLogger;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface */
    private $fraudResponse;

    /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenRuleInterface */
    private $fraudScreenRule;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\HttpText  */
    private $httpTextFactory;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\ReportingSignature\ReportingSignatureStrategyContext */
    private $hashStrategy;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\ReportingSignature\ReportingHashingStrategyInterface */
    private $hashingAlgorithm;

    /**
     * Reporting constructor.
     * @param ApiExceptionFactory $apiExceptionFactory
     * @param Config $config
     * @param Logger $suiteLogger
     * @param ObjectManager $objectManager
     * @param FraudScreenResponseInterfaceFactory $fraudResponse
     * @param FraudScreenRuleInterfaceFactory $fraudScreenRule
     */
    public function __construct(
        HttpTextFactory $httpTextFactory,
        ApiExceptionFactory $apiExceptionFactory,
        Config $config,
        Logger $suiteLogger,
        ObjectManager $objectManager,
        FraudScreenResponseInterfaceFactory $fraudResponse,
        FraudScreenRuleInterfaceFactory $fraudScreenRule,
        ReportingSignatureStrategyContextFactory $hashStrategy
    ) {
        $this->config              = $config;
        $this->httpTextFactory     = $httpTextFactory;
        $this->apiExceptionFactory = $apiExceptionFactory;
        $this->suiteLogger         = $suiteLogger;
        $this->objectManager       = $objectManager;
        $this->fraudResponse       = $fraudResponse;
        $this->fraudScreenRule     = $fraudScreenRule;
        $this->hashStrategy        = $hashStrategy;
    }

    /**
     * Returns url for each environment according the configuration.
     */
    private function _getServiceUrl()
    {
        if ($this->config->getMode() == Config::MODE_LIVE) {
            return Config::URL_REPORTING_API_LIVE;
        } elseif ($this->config->getMode() == Config::MODE_DEVELOPMENT) {
            return Config::URL_REPORTING_API_DEV;
        } else {
            return Config::URL_REPORTING_API_TEST;
        }
    }

    /**
     * Creates the connection's signature.
     *
     * @param string $command Param request to the API.
     * @param string $params
     * @return string MD5|SHA256 hash signature.
     */
    private function _getXmlSignature($command, $params)
    {
        $xml = '<command>' . $command . '</command>';
        $xml .= '<vendor>' . $this->config->getVendorname() . '</vendor>';
        $xml .= '<user>' . $this->config->getReportingApiUser() . '</user>';
        $xml .= $params;
        $xml .= $this->makeHashingAlgorithm()->algorithmSignature();
        $xml .= '<password>' . $this->config->getReportingApiPassword() . '</password>';

        return $this->makeHashingAlgorithm()->hash($xml);
    }

    /**
     * Creates the xml file to be used into the request.
     *
     * @param string $command API command.
     * @param string $params Parameters used for each command.
     * @return string Xml string to be used into the API connection.
     */
    private function _createXml($command, $params = null)
    {
        $xml = '';
        $xml .= '<vspaccess>';
        $xml .= '<command>' . $command . '</command>';
        $xml .= '<vendor>' . $this->config->getVendorname() . '</vendor>';
        $xml .= '<user>' . $this->config->getReportingApiUser() . '</user>';

        if ($params !== null) {
            $xml .= $params;
        }

        $xml .= $this->makeHashingAlgorithm()->algorithmSignature();

        $xml .= '<signature>' . $this->_getXmlSignature($command, $params) . '</signature>';
        $xml .= '</vspaccess>';

        return $xml;
    }

    /**
     * @return Ebizmarts\SagePaySuite\Model\Api\ReportingSignature\ReportingHashingStrategyInterface
     */
    private function makeHashingAlgorithm()
    {
        if ($this->hashingAlgorithm == null) {
            $algorithmConfig = $this->config->getAdvancedValue("reporting_api_hash_algorithm");
            $this->hashingAlgorithm = $this->hashStrategy->create()->getStrategy($algorithmConfig);
        }

        return $this->hashingAlgorithm;
    }

    /**
     * @param $response
     * @return mixed
     * @throws
     */
    private function _handleApiErrors($response)
    {
        //parse xml as object
        $response = (object)((array)$response);

        $exceptionPhrase = "Invalid response from Elavon API.";
        $exceptionCode = 0;
        $validResponse = false;
        $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $response, [__METHOD__, __LINE__]);

        if (!empty($response)) {
            if (is_object($response) && !property_exists($response, "errorcode") || $response->errorcode == '0000') {
                //this is a successfull response
                return $response;
            } else { //there was an error
                if (is_object($response) && property_exists($response, "errorcode")) {
                    $exceptionCode = $response->errorcode;
                    if (property_exists($response, "error")) {
                        $exceptionPhrase = $response->error;
                        $validResponse = true;
                    }
                }
            }
        }

        if (!$validResponse) {
            $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $response, [__METHOD__, __LINE__]);
        }

        /** @var $exception \Ebizmarts\SagePaySuite\Model\Api\ApiException */
        $exception = $this->apiExceptionFactory->create([
            'phrase' => __($exceptionPhrase),
            'code' => $exceptionCode
        ]);

        throw $exception;
    }

    /**
     * This command returns all information held in Sage Pay about the specified transaction.
     *
     * @param string $vpstxid
     * @param null|int $storeId
     * @return mixed
     * @throws ApiException
     */
    public function getTransactionDetailsByVpstxid($vpstxid, $storeId = null)
    {
        $this->config->setConfigurationScopeId($storeId);

        $params = '<vpstxid>' . $vpstxid . '</vpstxid>';
        $xml = $this->_createXml('getTransactionDetail', $params);
        return $this->_handleApiErrors($this->_executeRequest($xml));
    }

    /**
     * @param $vendorTxCode
     * @param null|int $storeId
     * @return mixed
     * @throws ApiException
     */
    public function getTransactionDetailsByVendorTxCode($vendorTxCode, $storeId = null)
    {
        $this->config->setConfigurationScopeId($storeId);

        $params = '<vendorTxCode>' . $vendorTxCode . '</vendorTxCode>';
        $xml = $this->_createXml('getTransactionDetail', $params);
        return $this->_handleApiErrors($this->_executeRequest($xml));
    }

    public function whitelistIpAddress($ipAddress)
    {
        $params = "<validips><ipaddress><address>$ipAddress</address>";
        $params .= "<mask>" . self::DEFAULT_SUBNET_MASK . "</mask></ipaddress></validips>";
        $xml = $this->_createXml('addValidIPs', $params);
        return $this->_handleApiErrors($this->_executeRequest($xml));
    }

    /**
     * This command returns the number of tokens the vendor currently has.
     *
     * @return mixed
     * @throws
     */
    public function getTokenCount()
    {
        $params = '';
        $xml = $this->_createXml('getTokenCount', $params);
        return $this->_handleApiErrors($this->_executeRequest($xml));
    }

    /**
     * This command returns the fraud screening details for a particular transaction.
     * The recommendation is returned along with details of the specific fraud rules
     * triggered by the transaction.
     *
     * @param $vpstxid
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    public function getFraudScreenDetail($vpstxid, $storeId = null)
    {
        $this->config->setConfigurationScopeId($storeId);

        $params = '<vpstxid>' . $vpstxid . '</vpstxid>';
        $xmlRequest = $this->_createXml('getFraudScreenDetail', $params);

        $result = $this->_handleApiErrors($this->_executeRequest($xmlRequest));

        $fraudResponse = $this->fraudResponse->create();

        $fraudResponse->setErrorCode((string)$result->errorcode);
        $fraudResponse->setTimestamp((string)$result->timestamp);
        $fraudResponse->setFraudProviderName((string)$result->fraudprovidername);

        if ($fraudResponse->getErrorCode() == '0000') {
            if ($fraudResponse->getFraudProviderName() == Fraud::RED) {
                $fraudResponse->setFraudScreenRecommendation((string)$result->fraudscreenrecommendation);
                $fraudResponse->setFraudId((string)$result->fraudid);
                $fraudResponse->setFraudCode((string)$result->fraudcode);
                $fraudResponse->setFraudCodeDetail((string)$result->fraudcodedetail);
            } elseif ($fraudResponse->getFraudProviderName() == Fraud::FSG) {
                $fraudResponse->setThirdmanId((string)$result->t3mid);
                $fraudResponse->setThirdmanScore((string)$result->t3mscore);
                $fraudResponse->setThirdmanAction((string)$result->t3maction);

                $rules = [];
                if (isset($result->t3mresults)) {
                    foreach ($result->t3mresults as $_rule) {
                        $ruleDescription = !empty($_rule->description) ? $_rule->description : '';
                        $ruleScore = !empty($_rule->score) ? $_rule->score : '';
                        $fraudRule = $this->fraudScreenRule->create();
                        $fraudRule->setDescription((string)$ruleDescription);
                        $fraudRule->setScore((string)$ruleScore);
                        $rules[] = $fraudRule;
                    }
                }

                $fraudResponse->setThirdmanRules($rules);
            }
        }

        return $fraudResponse;
    }

    /**
     * Get version of Reporting API (Used to validate credentials)
     *
     * @return mixed
     * @throws
     */
    public function getVersion()
    {
        $xml = $this->_createXml('version');
        return $this->_handleApiErrors($this->_executeRequest($xml));
    }

    /**
     * @param $xml
     * @return bool|\SimpleXMLElement
     */
    private function _executeRequest($xml)
    {
        /** @var \Ebizmarts\SagePaySuite\Model\Api\HttpText $rest */
        $rest = $this->httpTextFactory->create();
        $rest->setUrl($this->_getServiceUrl());
        $response = $rest->executePost('XML=' . $xml);

        if ($response->getResponseData() === false) {
            return false;
        }
        $data = preg_split('/^\r?$/m', $response->getResponseData(), 2);
        $data = trim($data[1]);

        try {
            $xml = $this->objectManager->create('\SimpleXMLElement', ['data' => $data]);
        } catch (\Exception $e) {
            return false;
        }

        return $xml;
    }

    /**
     * Use the getTransactionList command to return a list of all transactions
     * started between the specific dates for the given vendor.
     * You can filter on transaction type, user name and success and failure.
     * By default, this command will return all successful transaction information,
     * for all transaction types, between the specified dates.
     */
    public function getTransactionList($vdorTxCode, $startdate, $storeId = null)
    {
        $this->config->setConfigurationScopeId($storeId);
        $strDate = strtotime($startdate);
        $dateFrom = date("d/m/Y H:i:s", $strDate);
        $dateTo = date("d/m/Y H:i:s");
        $params = "<vendortxcode>$vdorTxCode</vendortxcode><startdate>$dateFrom</startdate><enddate>$dateTo</enddate>";
        $xml = $this->_createXml('getTransactionList', $params);
        return $this->_handleApiErrors($this->_executeRequest($xml));
    }
}
