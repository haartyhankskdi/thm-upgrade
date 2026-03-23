<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/27/17
 * Time: 10:11 AM
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class PiInstructionRequest extends AbstractExtensibleModel implements PiInstructionRequestInterface
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
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($amount)
    {
        $this->setData(self::AMOUNT, $amount);
    }
    public function __toArray(): array
    {
        return [
            self::INSTRUCTION_TYPE => $this->getInstructionType(),
            self::AMOUNT => $this->getAmount()
        ];
    }
}
