<?php

namespace Mw\Extension\Model;

use Mw\Extension\Api\PaymentInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Zend\Log\Writer\Stream;

use Zend\Log\Logger;

class ApiPayment implements PaymentInterface
{
    protected $curl;
    protected $scopeConfig;

    public function __construct(
        // \Magento\Framework\Webapi\Rest\Request $request
        Curl $curl,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        // $this->request = $request;
    }
    public function getMerchantKey()
    {
        $integrationKey = $this->scopeConfig->getValue('opayo/general/key');
        $integrationPassword = $this->scopeConfig->getValue('opayo/general/password');
        $token = base64_encode($integrationKey . ':' . $integrationPassword);
        // Set the request URL
        $url = $this->scopeConfig->getValue('opayo/general/url');
        $url = $url . 'merchant-session-keys';

        // Encode the key and password to Base64

        // Prepare headers
        $headers = [
            'Authorization' => 'Basic ' . $token,
            'Content-Type'  => 'application/json',
        ];

        // Set the POST data
        $postData = json_encode([
            'vendorName' => 'tariqhalalmeat'
        ]);

        try {
            // Set the headers
            $this->curl->setHeaders($headers);

            // Make the POST request
            $this->curl->post($url, $postData);

            // Get the response
            $response = $this->curl->getBody();

            //return the response
            return $response;
        } catch (\Exception $e) {
            // Handle any potential errors
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/app/getMerchantKey.log');

            $logger = new \Zend\Log\Logger();

            $logger->addWriter($writer);

            $logger->info('---------------------------------------------------------------------');

            $logger->info($e->getMessage());
            
            return ['error' => $e->getMessage()];
        }
    }
}
