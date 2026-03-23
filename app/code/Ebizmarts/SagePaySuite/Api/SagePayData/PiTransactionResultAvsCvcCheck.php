<?php
/**
 * Copyright © 2020 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class PiTransactionResultAvsCvcCheck extends AbstractExtensibleModel implements PiTransactionResultAvsCvcCheckInterface
{

    /**
     * @param string $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param string $addressStatus
     * @return void
     */
    public function setAddress($addressStatus)
    {
        $this->setData(self::ADDRESS, $addressStatus);
    }

    /**
     * @return string|null
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @param string $postalCodeStatus
     * @return void
     */
    public function setPostalCode($postalCodeStatus)
    {
        $this->setData(self::POSTAL_CODE, $postalCodeStatus);
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->getData(self::POSTAL_CODE);
    }

    /**
     * @param string $securityCodeStatus
     * @return void
     */
    public function setSecurityCode($securityCodeStatus)
    {
        $this->setData(self::SECURITY_CODE, $securityCodeStatus);
    }

    /**
     * @return string|null
     */
    public function getSecurityCode()
    {
        return $this->getData(self::SECURITY_CODE);
    }
    public function __toArray(): array
    {
        return [
            self::STATUS => $this->getStatus(),
            self::ADDRESS => $this->getAddress(),
            self::POSTAL_CODE => $this->getPostalCode(),
            self::SECURITY_CODE => $this->getSecurityCode()
        ];
    }
}
