<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/25/17
 * Time: 6:20 PM
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class PiTransactionResultThreeD extends AbstractExtensibleModel implements PiTransactionResultThreeDInterface
{
    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }
    public function __toArray(): array
    {
        return [
            self::STATUS => $this->getStatus()
        ];
    }
}
