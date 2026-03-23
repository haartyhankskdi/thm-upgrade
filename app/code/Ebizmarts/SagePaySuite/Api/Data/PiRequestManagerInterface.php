<?php
namespace Ebizmarts\SagePaySuite\Api\Data;

interface PiRequestManagerInterface extends PiRequestInterface
{
    public const MODE           = 'mode';
    public const QUOTE          = 'quote';
    public const VENDOR_NAME    = 'vendor_name';
    public const VENDOR_TX_CODE = 'vendor_tx_code';
    public const PAYMENT_ACTION = 'payment_action';
    public const CRES           = 'cres';
    public const TRANSACTION_ID = 'transaction_id';
    public const ORDER_IDS      = 'order_ids';

    /**
     * @return string
     */
    public function getCres();

    /**
     * @param string $cRes
     * @return void
     */
    public function setCres($cRes);

    /**
     * @param string $transactionId
     * @return void
     */
    public function setTransactionId($transactionId);

    /**
     * @return string
     */
    public function getTransactionId();

    /**
     * Transaction mode: test or live.
     * @return string
     */
    public function getMode();

    /**
     * @param string $mode
     * @return void
     */
    public function setMode($mode);

    /**
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuote();

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    public function setQuote($quote);

    /**
     * @return string
     */
    public function getVendorName();

    /**
     * @param string $vendorName
     * @return void
     */
    public function setVendorName($vendorName);

    /**
     * @return string
     */
    public function getVendorTxCode();

    /**
     * @param string $vendorTxCode
     * @return void
     */
    public function setVendorTxCode($vendorTxCode);

    /**
     * @return string
     */
    public function getPaymentAction();

    /**
     * @param string $paymentAction
     * @return void
     */
    public function setPaymentAction($paymentAction);
}
