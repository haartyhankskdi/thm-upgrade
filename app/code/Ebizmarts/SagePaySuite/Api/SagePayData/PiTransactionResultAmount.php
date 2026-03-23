<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/26/17
 * Time: 11:34 AM
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class PiTransactionResultAmount extends AbstractExtensibleModel implements PiTransactionResultAmountInterface
{

    /**
     * @inheritDoc
     */
    public function getTotalAmount()
    {
        return $this->getData(self::TOTAL_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setTotalAmount($amount)
    {
        $this->setData(self::TOTAL_AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getSaleAmount()
    {
        return $this->getData(self::SALE_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setSaleAmount($amount)
    {
        $this->setData(self::SALE_AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getSurchargeAmount()
    {
        return $this->getData(self::SURCHARGE_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setSurchargeAmount($amount)
    {
        $this->setData(self::SURCHARGE_AMOUNT, $amount);
    }
    public function __toArray(): array
    {
        return [
            self::TOTAL_AMOUNT => $this->getTotalAmount(),
            self::SALE_AMOUNT => $this->getSaleAmount(),
            self::SURCHARGE_AMOUNT => $this->getSurchargeAmount()
        ];
    }
}
