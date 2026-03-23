<?php

namespace Ebizmarts\SagePaySuite\Model\Api;

use Ebizmarts\SagePaySuite\Model\Config as SagePayConfig;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Laminas\Http\Request as HttpRequest;

abstract class Http
{
    /** @var string */
    private $basicAuth;

    /** @var string */
    private $contentType;

    /** @var string */
    private $responseData;

    /** @var string */
    private $destinationUrl;

    /** @var \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface */
    private $returnData;

    /** @var integer */
    private $responseCode;

    /** @var \Magento\Framework\HTTP\Adapter\Curl */
    private $curl;

    /** @var Logger */
    private $logger;

    /** @var SagePayConfig $sagePayConfig */
    protected $sagePayConfig;

    public function __construct(
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface $returnData,
        Logger $logger,
        SagePayConfig $sagePayConfig
    ) {
        $this->curl        = $curl;
        $this->returnData  = $returnData;
        $this->logger      = $logger;
        $this->sagePayConfig = $sagePayConfig;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return integer
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @return string
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * @return \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface
     */
    public function getReturnData()
    {
        return $this->returnData;
    }

    public function setBasicAuth($username, $password)
    {
        $this->basicAuth = "$username:$password";
    }

    protected function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    public function setUrl($url)
    {
        $this->destinationUrl = $url;
    }

    public function initialize()
    {
        $config = [
            'timeout'    => 120,
            'verifyhost' => 2,
        ];

        if ($this->basicAuth !== null) {
            $config['userpwd'] = $this->basicAuth;
        }

        $this->curl->setConfig($config);
    }

    /**
     * @param $body
     * @return \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface
     */
    public function executePost($body)
    {
        $this->initialize();

        $formattedBody = $body;
        if ($formattedBody[0] !== '{' && $this->sagePayConfig->getPreventPersonalDataLogging()) {
            $formattedBody = $this->stringToArray($formattedBody);
        } else {
            $formattedBody = str_replace("&", "&\r\n", $formattedBody);
            if ($formattedBody[0] === '{') {
                $formattedBody = json_decode($formattedBody);
            }
        }

        $this->getLogger()->sageLog(Logger::LOG_REQUEST, $formattedBody, [__METHOD__, __LINE__]);

        // @codingStandardsIgnoreStart
        $this->curl->write(
            HttpRequest::METHOD_POST,
            $this->destinationUrl,
            HttpRequest::VERSION_11,
            ['Content-type: ' . $this->contentType],
            $body
        );
        // @codingStandardsIgnoreEnd
        $this->responseData = $this->curl->read();

        $this->responseCode = $this->curl->getInfo(CURLINFO_HTTP_CODE);
        $this->curl->close();

        return $this->processResponse();
    }

    /**
     * @return \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface
     */
    public function executeGet()
    {
        $this->initialize();

        // @codingStandardsIgnoreStart
        $this->curl->write(
            HttpRequest::METHOD_GET,
            $this->destinationUrl,
            HttpRequest::VERSION_11,
            ['Content-type: ' . $this->contentType]
        );
        // @codingStandardsIgnoreEnd
        $this->responseData = $this->curl->read();

        $this->getLogger()->sageLog(Logger::LOG_REQUEST, $this->destinationUrl, [__METHOD__, __LINE__]);
        $this->getLogger()->sageLog(Logger::LOG_REQUEST, $this->responseData, [__METHOD__, __LINE__]);

        $this->responseCode = $this->curl->getInfo(CURLINFO_HTTP_CODE);
        $this->curl->close();

        return $this->processResponse();
    }

    /**
     * @return \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    abstract public function processResponse();

    private function stringToArray($string)
    {
        $queryArray = [];

        try {
            $queryPairs = explode('&', $string);

            foreach ($queryPairs as $pair) {
                if (strpos($pair, '=') !== false) {
                    list($key, $value) = explode('=', $pair);
                    $value = urldecode($value);
                    $queryArray[$key] = $value;
                }
            }
        } catch (\Exception $exception) {
            $this->getLogger()->sageLog(Logger::LOG_EXCEPTION, $exception->getMessage(), [__METHOD__, __LINE__]);
        } finally {
            return $queryArray;
        }
    }
}
