<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class PiRefundRequest extends AbstractExtensibleModel implements PiRefundRequestInterface
{
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
    public function setTransactionType()
    {
        $this->setData(self::TRANSACTION_TYPE, 'Refund');
    }

    /**
     * @inheritDoc
     */
    public function getReferenceTransactionId()
    {
        return $this->getData(self::REF_TRANSACTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setReferenceTransactionId($transactionId)
    {
        $this->setData(self::REF_TRANSACTION_ID, $transactionId);
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
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
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
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($desc)
    {
        $this->setData(self::DESCRIPTION, $desc);
    }
    public function __toArray(): array
    {
        return [
            self::TRANSACTION_TYPE => $this->getTransactionType(),
            self::REF_TRANSACTION_ID => $this->getReferenceTransactionId(),
            self::VENDOR_TX_CODE => $this->getVendorTxCode(),
            self::AMOUNT => $this->getAmount(),
            self::DESCRIPTION => $this->getDescription()
        ];
    }
}
