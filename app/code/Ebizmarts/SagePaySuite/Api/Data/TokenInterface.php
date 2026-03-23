<?php

namespace Ebizmarts\SagePaySuite\Api\Data;

interface TokenInterface
{
    public const CUSTOMER_ID = 'customer_id';
    public const TOKEN = 'token';
    public const CC_LAST_FOUR = 'cc_last_4';
    public const CC_EXP_MONTH = 'cc_exp_month';
    public const CC_TYPE = 'cc_type';
    public const CC_EXP_YEAR = 'cc_exp_year';
    public const VENDOR_NAME = 'vendorname';
    public const CREATED_AT = 'created_at';
    public const STORE_ID = 'store_id';
    public const PAYMENT_METHOD = 'payment_method';
    public const VAULT_ID = 'vault_id';

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     * @return void
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getToken();

    /**
     * @param string $token
     * @return void
     */
    public function setToken($token);

    /**
     * @return string
     */
    public function getCcLastFour();

    /**
     * @param string $ccLastFour
     * @return void
     */
    public function setCcLastFour($ccLastFour);

    /**
     * @return string
     */
    public function getCcExpMonth();

    /**
     * @param string $ccExpMonth
     * @return void
     */
    public function setCcExpMonth($ccExpMonth);

    /**
     * @return string
     */
    public function getCcType();

    /**
     * @param string $ccType
     * @return void
     */
    public function setCcType($ccType);

    /**
     * @return string
     */
    public function getCcExpYear();

    /**
     * @param string $ccExpYear
     * @return void
     */
    public function setCcExpYear($ccExpYear);

    /**
     * @return string
     */
    public function getVendroName();

    /**
     * @param string $vendroName
     * @return void
     */
    public function setVendroName($vendroName);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt($createdAt);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return void
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getPaymentMethod();

    /**
     * @param string $paymentMethod
     * @return void
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * @return int
     */
    public function getVaultId();

    /**
     * @param int $vaultId
     * @return void
     */
    public function setVaultId($vaultId);
}
