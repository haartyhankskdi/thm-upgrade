<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/27/17
 * Time: 5:29 PM
 */

namespace Ebizmarts\SagePaySuite\Api\Data;

use \Ebizmarts\SagePaySuite\Api\Data\PiRequest;

class PiRequestManager extends PiRequest implements PiRequestManagerInterface
{
    /**
     * @inheritDoc
     */
    public function getMode()
    {
        return $this->getData(self::MODE);
    }

    /**
     * @inheritDoc
     */
    public function setMode($mode)
    {
        $this->setData(self::MODE, $mode);
    }

    /**
     * @inheritDoc
     */
    public function getQuote()
    {
        return $this->getData(self::QUOTE);
    }

    /**
     * @inheritDoc
     */
    public function setQuote($quote)
    {
        $this->setData(self::QUOTE, $quote);
    }

    /**
     * @inheritDoc
     */
    public function getVendorName()
    {
        return $this->getData(self::VENDOR_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setVendorName($vendorName)
    {
        $this->setData(self::VENDOR_NAME, $vendorName);
    }

    /**
     * @inheritDoc
     */
    public function getVendorTxCode()
    {
        return $this->getData(self::VENDOR_TX_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setVendorTxCode($vendorTxCode)
    {
        $this->setData(self::VENDOR_TX_CODE, $vendorTxCode);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentAction()
    {
        return $this->getData(self::PAYMENT_ACTION);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentAction($paymentAction)
    {
        $this->setData(self::PAYMENT_ACTION, $paymentAction);
    }

    /**
     * @param string $transactionId
     * @return void
     */
    public function setTransactionId($transactionId)
    {
        $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * @return string
     */
    public function getCres()
    {
        return $this->getData(self::CRES);
    }

    /**
     * @param string $cRes
     * @return void
     */
    public function setCres($cRes)
    {
        $this->setData(self::CRES, $cRes);
    }

    /**
     * @return array|null
     */
    public function getOrderIds()
    {
        return $this->getData(self::ORDER_IDS);
    }

    /**
     * @param array $orderIds
     */
    public function setOrderIds($orderIds)
    {
        $this->setData(self::ORDER_IDS, $orderIds);
    }
    public function __toArray(): array
    {
        return array_merge(parent::__toArray(), [
            self::MODE => $this->getMode(),
            self::QUOTE => $this->getQuote(),
            self::VENDOR_NAME => $this->getVendorName(),
            self::VENDOR_TX_CODE => $this->getVendorTxCode(),
            self::PAYMENT_ACTION => $this->getPaymentAction(),
            self::TRANSACTION_ID => $this->getTransactionId(),
            self::CRES => $this->getCres(),
            self::ORDER_IDS => $this->getOrderIds()
        ]);
    }
}
