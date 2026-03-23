<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

interface FraudScreenResponseInterface
{
    public const ERROR_CODE                  = 'errorcode';
    public const TIMESTAMP                   = 'timestamp';
    public const FRAUD_PROVIDER_NAME         = 'fraudprovidername';
    public const FRAUD_SCREEN_RECOMMENDATION = 'fraudscreenrecommendation';
    public const FRAUD_ID                    = 'fraudid';
    public const FRAUD_CODE                  = 'fraudcode';
    public const FRAUD_CODE_DETAIL           = 'fraudcodedetail';
    public const THIRDMAN_RULES              = 't3mresults';
    public const THIRDMAN_ID                 = 't3mid';
    public const THIRDMAN_SCORE              = 't3mscore';
    public const THIRDMAN_ACTION             = 't3maction';

    /**
     * @return string
     */
    public function getTimestamp();

    /**
     * @return string
     */
    public function getErrorCode();

    /**
     * @return string Either ReD or T3M.
     */
    public function getFraudProviderName();

    /**
     * ReD.
     * @return string ACCEPT, DENY, CHALLENGE or NOTCHECKED.
     */
    public function getFraudScreenRecommendation();

    /**
     * ReD.
     * @return string The unique ID from this transaction on the fraud detail.
     */
    public function getFraudId();

    /**
     * ReD.
     * @return string The fraud provider’s response code.
     */
    public function getFraudCode();

    /**
     * ReD.
     * @return string A human-readable explanation of the fraud code.
     */
    public function getFraudCodeDetail();

    /**
     * T3M.
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenRuleInterface[]
     * The rules that cause the transaction to be denied.
     */
    public function getThirdmanRules();

    /**
     * @return array
     */
    public function getThirdmanRulesAsArray();

    /**
     * T3M.
     * @return string
     */
    public function getThirdmanId();

    /**
     * T3M.
     * @return string
     */
    public function getThirdmanScore();

    /**
     * T3M.
     * @return string OK, HOLD, REJECT or NORESULT.
     */
    public function getThirdmanAction();

    /**
     * @param $timestamp
     * @return void
     */
    public function setTimestamp($timestamp);

    /**
     * @param $errorCode
     * @return void
     */
    public function setErrorCode($errorCode);

    /**
     * @param $fraudProviderName
     * @return void
     */
    public function setFraudProviderName($fraudProviderName);

    /**
     * @param $fraudScreenRecommendation
     * @return void
     */
    public function setFraudScreenRecommendation($fraudScreenRecommendation);

    /**
     * @param $fraudId
     * @return void
     */
    public function setFraudId($fraudId);

    /**
     * @param $fraudCode
     * @return void
     */
    public function setFraudCode($fraudCode);

    /**
     * @param $fraudCodeDetail
     * @return void
     */
    public function setFraudCodeDetail($fraudCodeDetail);

    /**
     * T3M.
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenRuleInterface[]
     * @return void
     */
    public function setThirdmanRules($thirdmanRules);

    /**
     * @param $thirdmanId
     * @return void
     */
    public function setThirdmanId($thirdmanId);

    /**
     * @param $thirdmanScore
     * @return void
     */
    public function setThirdmanScore($thirdmanScore);

    /**
     * @param $thirdmanAction
     * @return void
     */
    public function setThirdmanAction($thirdmanAction);
}
