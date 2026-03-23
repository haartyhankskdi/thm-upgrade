<?php
namespace Ebizmarts\SagePaySuite\Api\Data;

interface FormResultInterface extends ResultInterface
{
    public const REDIRECT_URL = 'redirect_url';
    public const VPS_PROTOCOL = 'vps_protocol';
    public const TX_TYPE      = 'tx_type';
    public const VENDOR       = 'vendor';
    public const CRYPT        = 'crypt';

    /**
     * @return string
     */
    public function getRedirectUrl();

    /**
     * @param $url
     * @return void
     */
    public function setRedirectUrl($url);

    /**
     * @return string
     */
    public function getVpsProtocol();

    /**
     * @param string $protocolVersion
     * @return void
     */
    public function setVpsProtocol($protocolVersion);

    /**
     * @return string
     */
    public function getTxType();

    /**
     * @param string $txType
     * @return void
     */
    public function setTxType($txType);

    /**
     * @return string
     */
    public function getVendor();

    /**
     * @param string $vendorname
     * @return void
     */
    public function setVendor($vendorname);

    /**
     * @return string
     */
    public function getCrypt();

    /**
     * @param string $crypt
     * @return void
     */
    public function setCrypt($crypt);
}
