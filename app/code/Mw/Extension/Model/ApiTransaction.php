<?php

namespace Mw\Extension\Model;

use Mw\Extension\Api\TransactionInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Zend\Log\Writer\Stream;

use Zend\Log\Logger;

class ApiTransaction implements TransactionInterface
{
    protected $curl;
    protected $scopeConfig;
     protected $request;
    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        Curl $curl,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }
    public function getTransaction()
    {
        $integrationKey = $this->scopeConfig->getValue('opayo/general/key');
        $integrationPassword = $this->scopeConfig->getValue('opayo/general/password');
        $token = base64_encode($integrationKey . ':' . $integrationPassword);
        // Set the request URL
        $url = $this->scopeConfig->getValue('opayo/general/url');
        $url = $url . 'transactions';
        // Encode the key and password to Base64
        $token = base64_encode($integrationKey . ':' . $integrationPassword);

        // Prepare headers
        $headers = [
            'Authorization' => 'Basic ' . $token,
            'Content-Type'  => 'application/json',
        ];

        $data = $this->request->getBodyParams();

        // Set the POST data
        $postData = json_encode($data);

        try {
            // Set the headers
            $this->curl->setHeaders($headers);

            // Make the POST request
            $this->curl->post($url, $postData);

            // Get the response
            $response = $this->curl->getBody();

            // return the response
            return $response;
        } catch (\Exception $e) {
            // Handle any potential errors
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/app/getTransaction.log');

            $logger = new \Zend\Log\Logger();

            $logger->addWriter($writer);

            $logger->info('---------------------------------------------------------------------');

            $logger->info($e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
