<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class PiInstructionResponse extends AbstractExtensibleModel implements PiInstructionResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getInstructionType()
    {
        return $this->getData(self::INSTRUCTION_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setInstructionType($instructionType)
    {
        $this->setData(self::INSTRUCTION_TYPE, $instructionType);
    }

    /**
     * @inheritDoc
     */
    public function getDate()
    {
        return $this->getData(self::DATE);
    }

    /**
     * @inheritDoc
     */
    public function setDate($date)
    {
        $this->setData(self::DATE, $date);
    }
    public function __toArray(): array
    {
        return [
            self::INSTRUCTION_TYPE => $this->getInstructionType(),
            self::DATE => $this->getDate()
        ];
    }
}
