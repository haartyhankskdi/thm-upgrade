<?php

namespace Ebizmarts\SagePaySuite\Api\Data;

class FormResult extends Result implements FormResultInterface
{

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->getData(self::REDIRECT_URL);
    }

    /**
     * @param $url
     * @return void
     */
    public function setRedirectUrl($url)
    {
        $this->setData(self::REDIRECT_URL, $url);
    }

    /**
     * @return string
     */
    public function getVpsProtocol()
    {
        return $this->getData(self::VPS_PROTOCOL);
    }

    /**
     * @param string $protocolVersion
     * @return void
     */
    public function setVpsProtocol($protocolVersion)
    {
        $this->setData(self::VPS_PROTOCOL, $protocolVersion);
    }

    /**
     * @return string
     */
    public function getTxType()
    {
        return $this->getData(self::TX_TYPE);
    }

    /**
     * @param string $txType
     * @return void
     */
    public function setTxType($txType)
    {
        $this->setData(self::TX_TYPE, $txType);
    }

    /**
     * @return string
     */
    public function getVendor()
    {
        return $this->getData(self::VENDOR);
    }

    /**
     * @param string $vendorname
     * @return void
     */
    public function setVendor($vendorname)
    {
        $this->setData(self::VENDOR, $vendorname);
    }

    /**
     * @return string
     */
    public function getCrypt()
    {
        return $this->getData(self::CRYPT);
    }

    /**
     * @param string $crypt
     * @return void
     */
    public function setCrypt($crypt)
    {
        $this->setData(self::CRYPT, $crypt);
    }
    public function __toArray(): array
    {
        return array_merge(parent::__toArray(), [
            self::REDIRECT_URL => $this->getRedirectUrl(),
            self::VPS_PROTOCOL => $this->getVpsProtocol(),
            self::TX_TYPE => $this->getTxType(),
            self::VENDOR => $this->getVendor(),
            self::CRYPT => $this->getCrypt()
        ]);
    }
}
