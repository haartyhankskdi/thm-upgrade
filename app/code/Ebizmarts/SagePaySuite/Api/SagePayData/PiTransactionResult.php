<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/25/17
 * Time: 4:05 PM
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class PiTransactionResult extends AbstractExtensibleModel implements PiTransactionResultInterface
{
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
    public function getTransactionType()
    {
        return $this->getData(self::TRANSACTION_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionType($transactionType)
    {
        $this->setData(self::TRANSACTION_TYPE, $transactionType);
    }

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
    public function getStatusCode()
    {
        return $this->getData(self::STATUS_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setStatusCode($statusCode)
    {
        $this->setData(self::STATUS_CODE, $statusCode);
    }

    /**
     * @inheritDoc
     */
    public function getStatusDetail()
    {
        return $this->getData(self::STATUS_DETAIL);
    }

    /**
     * @inheritDoc
     */
    public function setStatusDetail($statusDetail)
    {
        $this->setData(self::STATUS_DETAIL, $statusDetail);
    }

    /**
     * @inheritDoc
     */
    public function setRetrievalReference($ref)
    {
        $this->setData(self::RETRIEVAL_REFERENCE, $ref);
    }

    /**
     * @inheritDoc
     */
    public function getRetrievalReference()
    {
        return $this->getData(self::RETRIEVAL_REFERENCE);
    }

    /**
     * @inheritDoc
     */
    public function setBankResponseCode($code)
    {
        $this->setData(self::BANK_RESPONSE_CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getBankResponseCode()
    {
        return $this->getData(self::BANK_RESPONSE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setBankAuthCode($code)
    {
        $this->setData(self::BANK_AUTH_CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getBankAuthCode()
    {
        return $this->getData(self::BANK_AUTH_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getTxAuthNo()
    {
        return $this->getData(self::TX_AUTH_NO);
    }

    /**
     * @inheritDoc
     */
    public function setTxAuthNo($code)
    {
        $this->setData(self::TX_AUTH_NO, $code);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($amount)
    {
        $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setCurrency($currencyCode)
    {
        $this->setData(self::CURRENCY, $currencyCode);
    }

    /**
     * @inheritDoc
     */
    public function getCurrency()
    {
        return $this->getData(self::CURRENCY);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
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
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeDInterface
     */
    public function getThreeDSecure()
    {
        return $this->getData(self::THREED_SECURE);
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeDInterface $threed
     * @return void
     */
    public function setThreeDSecure($threed)
    {
        $this->setData(self::THREED_SECURE, $threed);
    }

    /**
     * @param $creq
     * @return void
     */
    public function setCReq($creq)
    {
        $this->setData(self::C_REQ, $creq);
    }

    /**
     * @return string
     */
    public function getCReq()
    {
        return $this->getData(self::C_REQ);
    }

    /**
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheckInterface
     */
    public function getAvsCvcCheck()
    {
        return $this->getData(self::AVS_CVC_CHECK);
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheckInterface $avsCvcCheck
     * @return void
     */
    public function setAvsCvcCheck($avsCvcCheck)
    {
        $this->setData(self::AVS_CVC_CHECK, $avsCvcCheck);
    }
    public function __toArray(): array
    {
        return [
            self::TRANSACTION_ID => $this->getTransactionId(),
            self::TRANSACTION_TYPE => $this->getTransactionType(),
            self::STATUS => $this->getStatus(),
            self::STATUS_CODE => $this->getStatusCode(),
            self::STATUS_DETAIL => $this->getStatusDetail(),
            self::RETRIEVAL_REFERENCE => $this->getRetrievalReference(),
            self::BANK_RESPONSE_CODE => $this->getBankResponseCode(),
            self::BANK_AUTH_CODE => $this->getBankAuthCode(),
            self::TX_AUTH_NO => $this->getTxAuthNo(),
            self::AMOUNT => $this->getAmount(),
            self::CURRENCY => $this->getCurrency(),
            self::PAYMENT_METHOD => $this->getPaymentMethod(),
            self::ACS_URL => $this->getAcsUrl(),
            self::THREED_SECURE => $this->getThreeDSecure(),
            self::C_REQ => $this->getCReq(),
            self::AVS_CVC_CHECK => $this->getAvsCvcCheck()
        ];
    }
}
