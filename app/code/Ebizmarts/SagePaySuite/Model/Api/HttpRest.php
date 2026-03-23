<?php

namespace Ebizmarts\SagePaySuite\Model\Api;

use Ebizmarts\SagePaySuite\Model\Config as SagePayConfig;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;

class HttpRest extends Http
{
    public function __construct(
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface $returnData,
        Logger $logger,
        SagePayConfig $sagePayConfig
    ) {
        parent::__construct($curl, $returnData, $logger, $sagePayConfig);

        $this->setContentType("application/json");
    }

    /**
     * @return \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    public function processResponse()
    {
        $this->getLogger()->sageLog(Logger::LOG_REQUEST, $this->getResponseData(), [__METHOD__, __LINE__]);

        $data = preg_split('/^\r?$/m', $this->getResponseData(), 2);
        $transactionId = $this->getTransactionId($data);
        $data = json_decode(trim($data[1]));

        $this->getLogger()->sageLog(Logger::LOG_REQUEST, $data, [__METHOD__, __LINE__]);
        $this->getLogger()->ascUrlLog($data);

        /** @var \Ebizmarts\SagePaySuite\Api\Data\HttpResponse $return */
        $this->getReturnData()->setStatus($this->getResponseCode());
        $this->getReturnData()->setResponseData($data);
        $this->getReturnData()->setTransactionId($transactionId);

        return $this->getReturnData();
    }

    private function getTransactionId($data)
    {
        $transactionId = null;
        try {
            if (isset($data[0]) && strpos($data[0], 'x-transaction-id:') !== false) {
                $valueOnArray = preg_grep(
                    '~' . preg_quote('x-transaction-id:', '~') . '~',
                    explode("\r\n", $data[0])
                );
                $strField = explode(": ", $valueOnArray[key($valueOnArray)]);
                $transactionId = $strField[1];
            }
        } catch (\Exception $exception) {
            return $transactionId;
        }

        return $transactionId;
    }
}
