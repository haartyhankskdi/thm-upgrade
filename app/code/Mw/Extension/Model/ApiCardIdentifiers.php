<?php

namespace Mw\Extension\Model;

use Mw\Extension\Api\CardIdentifiersInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Zend\Log\Writer\Stream;

use Zend\Log\Logger;

class ApiCardIdentifiers implements CardIdentifiersInterface
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
    public function getCardIdentifiers()
    {
        $data = $this->request->getBodyParams();

        $token = $data['merchantSessionKey'];

        // Set the request URL
        $url = $this->scopeConfig->getValue('opayo/general/url');
        $url = $url . 'card-identifiers';

        // Prepare headers
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ];


        // Set the POST data
        $postData = json_encode([
            'cardDetails' => $data['cardDetails']
        ]);

        try {
            // Set the headers
            $this->curl->setHeaders($headers);

            // Make the POST request
            $this->curl->post($url, $postData);

            // Get the response
            $response = $this->curl->getBody();

            // Decode and return the response
            return $response;
        } catch (\Exception $e) {
            // Handle any potential errors
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/app/getCardIdentifiers.log');

            $logger = new \Zend\Log\Logger();

            $logger->addWriter($writer);

            $logger->info('---------------------------------------------------------------------');

            $logger->info($e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
