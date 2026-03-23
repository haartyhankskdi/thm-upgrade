<?php

namespace Mw\Extension\Model;

use Mw\Extension\Api\TransactionDetailInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Zend\Log\Writer\Stream;

use Zend\Log\Logger;

class ApiTransactionDetail implements TransactionDetailInterface
{
    protected $curl;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        Curl $curl,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }
    public function getTransactionDetail()
    {
        // Encode the key and password to Base64
        $integrationKey = $this->scopeConfig->getValue('opayo/general/key');
        $integrationPassword = $this->scopeConfig->getValue('opayo/general/password');
        $token = base64_encode($integrationKey . ':' . $integrationPassword);

         // Get transactionId from request params
        $transactionId = $this->request->getParams();
        $transactionId= $transactionId['transactionId'];

        // Set the request URL
        $url = $this->scopeConfig->getValue('opayo/general/url');
        $url = $url . 'transactions/' . $transactionId;

        // Prepare headers
        $headers = [
            'Authorization' => 'Basic ' . $token,
            'Content-Type'  => 'application/json',
        ];


        try {
            // Set the headers
            $this->curl->setHeaders($headers);

            // Make the get request
            $this->curl->get($url);

            // Get the response
            $response = $this->curl->getBody();

            return $response;
        } catch (\Exception $e) {
            // Handle any potential errors
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/app/getTransactionDetail.log');

            $logger = new \Zend\Log\Logger();

            $logger->addWriter($writer);

            $logger->info('---------------------------------------------------------------------');

            $logger->info($e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }
}
