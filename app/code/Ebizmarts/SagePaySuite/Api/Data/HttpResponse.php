<?php
namespace Ebizmarts\SagePaySuite\Api\Data;

use Magento\Framework\Model\AbstractExtensibleModel;

class HttpResponse extends AbstractExtensibleModel implements HttpResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($httpStatusCode)
    {
        $this->setData(self::STATUS, $httpStatusCode);
    }

    /**
     * @inheritDoc
     */
    public function getResponseData()
    {
        return $this->getData(self::RESPONSE_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setResponseData($responseData)
    {
        $this->setData(self::RESPONSE_DATA, $responseData);
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionId($transactionId)
    {
        $this->setData(self::TRANSACTION_ID, $transactionId);
    }
    public function __toArray(): array
    {
        return [
            self::STATUS => $this->getStatus(),
            self::RESPONSE_DATA => $this->getResponseData(),
            self::TRANSACTION_ID => $this->getTransactionId()
        ];
    }
}
