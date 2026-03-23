<?php
namespace Ebizmarts\SagePaySuite\Api\Data;

interface HttpResponseInterface
{
    public const STATUS        = 'status';
    public const RESPONSE_DATA = 'response_data';
    public const TRANSACTION_ID = 'transaction_id';

    /**
     * @return integer
     */
    public function getStatus();

    /**
     * @param integer $httpStatusCode
     */
    public function setStatus($httpStatusCode);

    /**
     * @return string
     */
    public function getResponseData();

    /**
     * @param string $responseData
     */
    public function setResponseData($responseData);

    /**
     * @return string
     */
    public function getTransactionId();

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId);
}
