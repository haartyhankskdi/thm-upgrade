<?php
namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class FraudScreenRule extends AbstractExtensibleModel implements FraudScreenRuleInterface
{

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function getScore()
    {
        return $this->getData(self::SCORE);
    }

    /**
     * @inheritDoc
     */
    public function setScore($score)
    {
        $this->setData(self::SCORE, $score);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    public function __toArray(): array
    {
        return [
            self::DESCRIPTION => $this->getDescription(),
            self::SCORE => $this->getScore()
        ];
    }
}
