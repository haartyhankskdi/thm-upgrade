<?php
namespace Ebizmarts\SagePaySuite\Api\Data;

interface PiRequestInterface
{
    public const CARD_ID        = 'card_identifier';
    public const MSK            = 'merchant_session_key';
    public const CARD_LAST_FOUR = 'card_last_four';
    public const CARD_EXP_MONTH = 'card_exp_month';
    public const CARD_EXP_YEAR  = 'card_exp_year';
    public const CARD_TYPE      = 'card_type';
    public const SAVE_TOKEN     = 'save_token';
    public const REUSABLE_TOKEN = 'reusable_token';

    /**
     * @return string
     */
    public function getCardIdentifier();

    /**
     * @param string $cardId Card identifier.
     * @return void
     */
    public function setCardIdentifier($cardId);

    /**
     * @return string
     */
    public function getMerchantSessionKey();

    /**
     * @param string $msk
     * @return void
     */
    public function setMerchantSessionKey($msk);

    /**
     * @return int
     */
    public function getCcLastFour();

    /**
     * @param int $lastFour
     * @return void
     */
    public function setCcLastFour($lastFour);

    /**
     * @return int
     */
    public function getCcExpMonth();

    /**
     * @param int $expiryMonth
     * @return void
     */
    public function setCcExpMonth($expiryMonth);

    /**
     * @return int
     */
    public function getCcExpYear();

    /**
     * @param int $expiryYear
     * @return void
     */
    public function setCcExpYear($expiryYear);

    /**
     * @return string
     */
    public function getCcType();

    /**
     * @param string $cardType
     * @return void
     */
    public function setCcType($cardType);

    /**
     * @return bool
     */
    public function getSaveToken();

    /**
     * @param bool $saveToken
     * @return void
     */
    public function setSaveToken(bool $saveToken);

    /**
     * @return bool
     */
    public function getReusableToken();

    /**
     * @param bool $reusable
     * @return void
     */
    public function setReusableToken(bool $reusable);
}
