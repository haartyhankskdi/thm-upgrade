<?php

namespace Ebizmarts\BrippoPayments\Helper\BrippoApi;

use Ebizmarts\BrippoPayments\Exception\BrippoApiException;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Math\Random;

abstract class Service extends AbstractHelper
{
    const SERVICE_URL = 'https://api.brippo.com/';

    const PARAM_PI_ID = 'id';
    const PARAM_PI_CLIENT_SECRET = 'client_secret';
    const PARAM_PI_STATUS = 'status';
    const PARAM_DESTINATION_CHARGE = 'destinationCharge';
    const PARAM_PL_ID = 'id';
    const PARAM_MODE_TEST = 'test';
    const PARAM_MODE_LIVE = 'live';

    const PARAM_KEY_LIVEMODE = 'livemode';
    const PARAM_KEY_AMOUNT = 'amount';

    const PARAM_READER_ID = 'readerId';
    const PARAM_PROCESS_CONFIG = 'processConfig';

    protected $logger;
    protected $curl;
    protected $json;
    protected $dataHelper;
    protected $configWriter;
    protected $mathRandom;
    protected $stripeHelper;
    protected $debugMode = false;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param Curl $curl
     * @param Json $json
     * @param DataHelper $dataHelper
     * @param WriterInterface $configWriter
     * @param Random $mathRandom
     * @param Stripe $stripeHelper
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context         $context,
        Logger          $logger,
        Curl            $curl,
        Json            $json,
        DataHelper      $dataHelper,
        WriterInterface $configWriter,
        Random          $mathRandom,
        Stripe          $stripeHelper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->curl = $curl;
        $this->json = $json;
        $this->dataHelper = $dataHelper;
        $this->configWriter = $configWriter;
        $this->mathRandom = $mathRandom;
        $this->stripeHelper = $stripeHelper;
        $this->debugMode = $this->dataHelper->getStoreConfig(DataHelper::XML_PATH_DEBUG_MODE);
    }

    /**
     * @param string $serviceUrl
     * @param array $payload
     * @param array|null $extraHeaders
     * @return array
     * @throws LocalizedException
     */
    protected function curlPostRequest(string $serviceUrl, array $payload, ?array $extraHeaders = null): array
    {
        if ($this->debugMode) {
            $this->logger->log('POST ' . $serviceUrl, Logger::SERVICE_API_LOG);
            $this->logger->log($this->json->serialize($payload), Logger::SERVICE_API_LOG);
        }

        $headers = ["Content-Type: application/json"];
        if (!empty($extraHeaders)) {
            $headers = array_merge($headers, $extraHeaders);
        }

        $this->curl->write(
            'POST',
            $serviceUrl,
            '1.1',
            $headers,
            $this->json->serialize($payload)
        );

        $responseBody = $this->curl->read();
        $statusCode = $this->curl->getInfo(CURLINFO_HTTP_CODE);

        $this->curl->close();
        return $this->processResponse($responseBody, $statusCode);
    }

    /**
     * @param string $serviceUrl
     * @return array
     * @throws LocalizedException
     */
    protected function curlGetRequest(string $serviceUrl): array
    {
        if ($this->debugMode) {
            $this->logger->log('GET ' . $serviceUrl, Logger::SERVICE_API_LOG);
        }

        $this->curl->write('GET', $serviceUrl, '1.1', []);
        $responseBody = $this->curl->read();
        $statusCode = $this->curl->getInfo(CURLINFO_HTTP_CODE);

        $this->curl->close();
        return $this->processResponse($responseBody, $statusCode);
    }

    /**
     * @param string $responseBody
     * @param int $statusCode
     * @return array
     * @throws LocalizedException
     */
    protected function processResponse(string $responseBody, int $statusCode): array
    {
        $responseParts = explode("\r\n\r\n", $responseBody, 2);
        $body = isset($responseParts[1]) ? $responseParts[1] : $responseBody;

        if ($statusCode >= 400) {
            $error = $this->getErrorMessage($body);
            if (empty($error)) {
                // phpcs:disable
                $this->logger->log(print_r($body, true));
                // phpcs:enable
                $error = 'Invalid Brippo Api response status ' . $statusCode . '.';
            }

            throw new BrippoApiException(
                $error,
                'generic',
                $statusCode
            );
        }

        return $this->json->unserialize($body);
    }

    /**
     * @param $responseBody
     * @return mixed|null
     */
    protected function getErrorMessage($responseBody)
    {
        $error = null;
        try {
            $body = $this->json->unserialize($responseBody);
            if (isset($body['raw']['message'])) {
                $error = $body['raw']['message'];
            } elseif (isset($body['error'])) {
                $error = $body['error'];
            } elseif (isset($body['message'])) {
                $error = $body['message'];
            }
        } catch (Exception $ex) {
            $this->logger->log('Can not parse error response.');
        }
        return $error;
    }

    /**
     * @param bool $liveMode
     * @return string
     */
    protected function getMode(bool $liveMode): string
    {
        return $liveMode ? 'live' : 'test';
    }

    /**
     * @param $liveMode
     * @param $accountId
     * @param $apiKey
     * @return array
     */
    protected function getAuthHeaders($liveMode, $accountId, $apiKey): array
    {
        $headerParams = [];
        $headerParams []= "api_key: $apiKey";
        $headerParams []= "account_id: $accountId";
        $headerParams []= "live_mode: $liveMode";
        return $headerParams;
    }
}
