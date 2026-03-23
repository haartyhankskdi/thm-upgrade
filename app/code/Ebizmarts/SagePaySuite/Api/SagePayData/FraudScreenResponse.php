<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class FraudScreenResponse extends AbstractExtensibleModel implements FraudScreenResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getTimestamp()
    {
        return $this->getData(self::TIMESTAMP);
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode()
    {
        return $this->getData(self::ERROR_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getFraudProviderName()
    {
        return $this->getData(self::FRAUD_PROVIDER_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getFraudScreenRecommendation()
    {
        return $this->getData(self::FRAUD_SCREEN_RECOMMENDATION);
    }

    /**
     * @inheritDoc
     */
    public function getFraudId()
    {
        return $this->getData(self::FRAUD_ID);
    }

    /**
     * @inheritDoc
     */
    public function getFraudCode()
    {
        return $this->getData(self::FRAUD_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getFraudCodeDetail()
    {
        return $this->getData(self::FRAUD_CODE_DETAIL);
    }

    /**
     * @inheritDoc
     */
    public function getThirdmanRules()
    {
        return $this->getData(self::THIRDMAN_RULES);
    }

    /**
     * @inheritDoc
     */
    public function getThirdmanRulesAsArray()
    {
        $return = [];

        $data = $this->__toArray();

        if (isset($data[self::THIRDMAN_RULES])) {
            /** @var FraudScreenRuleInterface $rule */
            foreach ($data[self::THIRDMAN_RULES] as $rule) {
                $return []= $rule->getData();
            }
        }

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function getThirdmanId()
    {
        return $this->getData(self::THIRDMAN_ID);
    }

    /**
     * @inheritDoc
     */
    public function getThirdmanScore()
    {
        return $this->getData(self::THIRDMAN_SCORE);
    }

    /**
     * @inheritDoc
     */
    public function getThirdmanAction()
    {
        return $this->getData(self::THIRDMAN_ACTION);
    }

    /**
     * @inheritDoc
     */
    public function setTimestamp($timestamp)
    {
        $this->setData(self::TIMESTAMP, $timestamp);
    }

    /**
     * @inheritDoc
     */
    public function setErrorCode($errorCode)
    {
        $this->setData(self::ERROR_CODE, $errorCode);
    }

    /**
     * @inheritDoc
     */
    public function setFraudProviderName($fraudProviderName)
    {
        $this->setData(self::FRAUD_PROVIDER_NAME, $fraudProviderName);
    }

    /**
     * @inheritDoc
     */
    public function setFraudScreenRecommendation($fraudScreenRecommendation)
    {
        $this->setData(self::FRAUD_SCREEN_RECOMMENDATION, $fraudScreenRecommendation);
    }

    /**
     * @inheritDoc
     */
    public function setFraudId($fraudId)
    {
        $this->setData(self::FRAUD_ID, $fraudId);
    }

    /**
     * @inheritDoc
     */
    public function setFraudCode($fraudCode)
    {
        $this->setData(self::FRAUD_CODE, $fraudCode);
    }

    /**
     * @inheritDoc
     */
    public function setFraudCodeDetail($fraudCodeDetail)
    {
        $this->setData(self::FRAUD_CODE_DETAIL, $fraudCodeDetail);
    }

    /**
     * @inheritDoc
     */
    public function setThirdmanRules($thirdmanRules)
    {
        $this->setData(self::THIRDMAN_RULES, $thirdmanRules);
    }

    /**
     * @inheritDoc
     */
    public function setThirdmanId($thirdmanId)
    {
        $this->setData(self::THIRDMAN_ID, $thirdmanId);
    }

    /**
     * @inheritDoc
     */
    public function setThirdmanScore($thirdmanScore)
    {
        $this->setData(self::THIRDMAN_SCORE, $thirdmanScore);
    }

    /**
     * @inheritDoc
     */
    public function setThirdmanAction($thirdmanAction)
    {
        $this->setData(self::THIRDMAN_ACTION, $thirdmanAction);
    }

    public function __toArray(): array
    {
        return [
            self::TIMESTAMP => $this->getTimestamp(),
            self::ERROR_CODE => $this->getErrorCode(),
            self::FRAUD_PROVIDER_NAME => $this->getFraudProviderName(),
            self::FRAUD_SCREEN_RECOMMENDATION => $this->getFraudScreenRecommendation(),
            self::FRAUD_ID => $this->getFraudId(),
            self::FRAUD_CODE => $this->getFraudCode(),
            self::FRAUD_CODE_DETAIL => $this->getFraudCodeDetail(),
            self::THIRDMAN_RULES => $this->getThirdmanRules(),
            self::THIRDMAN_ID => $this->getThirdmanId(),
            self::THIRDMAN_SCORE => $this->getThirdmanScore(),
            self::THIRDMAN_ACTION => $this->getThirdmanAction()
        ];
    }
}
