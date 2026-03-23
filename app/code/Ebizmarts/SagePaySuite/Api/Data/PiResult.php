<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/25/17
 * Time: 5:35 PM
 */

namespace Ebizmarts\SagePaySuite\Api\Data;

class PiResult extends Result implements PiResultInterface
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
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
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

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId($quoteId)
    {
        $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @inheritDoc
     */
    public function getAcsUrl()
    {
        return $this->getData(self::ACS_URL);
    }

    /**
     * @inheritDoc
     */
    public function setAcsUrl($url)
    {
        $this->setData(self::ACS_URL, $url);
    }

    /**
     * @return string
     */
    public function getCreq()
    {
        return $this->getData(self::CREQ);
    }

    /**
     * @param $creq
     * @return void
     */
    public function setCreq($creq)
    {
        $this->setData(self::CREQ, $creq);
    }
    public function __toArray():array
    {
        return array_merge(parent::__toArray(), [
            self::STATUS => $this->getStatus(),
            self::TRANSACTION_ID => $this->getTransactionId(),
            self::ORDER_ID => $this->getOrderId(),
            self::QUOTE_ID => $this->getQuoteId(),
            self::ACS_URL => $this->getAcsUrl(),
            self::CREQ => $this->getCreq()
        ]);
    }
}
