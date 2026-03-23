<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class PiMerchantSessionKeyResponse extends AbstractExtensibleModel implements PiMerchantSessionKeyResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getMerchantSessionKey()
    {
        return $this->getData(self::MERCHANT_SESSION_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setMerchantSessionKey($key)
    {
        $this->setData(self::MERCHANT_SESSION_KEY, $key);
    }

    /**
     * @inheritDoc
     */
    public function getExpiry()
    {
        return $this->getData(self::EXPIRY);
    }

    /**
     * @inheritDoc
     */
    public function setExpiry($dateTime)
    {
        $this->setData(self::EXPIRY, $dateTime);
    }
    public function __toArray(): array
    {
        return [
            self::MERCHANT_SESSION_KEY => $this->getMerchantSessionKey(),
            self::EXPIRY => $this->getExpiry()
        ];
    }
}
