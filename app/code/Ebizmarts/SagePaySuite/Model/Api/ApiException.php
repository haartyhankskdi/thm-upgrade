<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class ApiException extends LocalizedException
{
    /**
     * Error code returned by SagePay
     */

    public const INVALID_SIGNATURE               = '0010';
    public const VALID_VALUE_REQUIRED            = '3002';
    public const API_INVALID_IP                  = '4020';
    public const INVALID_MERCHANT_AUTHENTICATION = '1002';
    public const MISSING_CRES                    = '1003';
    public const INVALID_USER_AUTH               = '0008';
    public const INVALID_TRANSACTION_STATE       = '5004';

    protected $transactionId;

    /**
     * @param Phrase $phrase
     * @param LocalizedException|null $cause
     * @param int $code
     * @param string $transactionId
     */
    public function __construct(Phrase $phrase, LocalizedException $cause = null, $code = 0, $transactionId = null)
    {
        parent::__construct($phrase, $cause);
        $this->code = $code;
        $this->transactionId = $transactionId;
    }

    /**
     * Get error message which can be displayed to website user
     *
     * @return \Magento\Framework\Phrase
     */
    public function getUserMessage()
    {
        switch ($this->getCode()) {
            case self::API_INVALID_IP:
                $message = __('Information received from an invalid IP address.');
                break;
            case self::INVALID_SIGNATURE:
                $message = __('Invalid signature. Please check Reporting API User and Password.');
                break;
            case self::VALID_VALUE_REQUIRED:
                if (strpos($this->getMessage(), "vpstxid") !== false) {
                    $message = __('Transaction NOT found / Invalid transaction Id.');
                } elseif (strpos($this->getMessage(), "username") !== false) {
                    $message = __('Invalid Opayo API credentials.');
                } else {
                    $message = __($this->getMessage());
                }
                break;
            case self::INVALID_MERCHANT_AUTHENTICATION:
                $message = __('Invalid merchant authentication.');
                break;
            case self::INVALID_USER_AUTH:
                $message = __('Your Opayo API user/password is invalid or the user might be locked out.');
                break;
            case self::MISSING_CRES:
                $message = __('Order not completed until valid challenge response');
                break;
            default:
                $message = __($this->getMessage());
                break;
        }
        return $message;
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
