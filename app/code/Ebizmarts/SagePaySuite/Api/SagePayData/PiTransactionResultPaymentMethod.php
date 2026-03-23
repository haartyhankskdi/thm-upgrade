<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/25/17
 * Time: 4:01 PM
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class PiTransactionResultPaymentMethod extends AbstractExtensibleModel implements
    PiTransactionResultPaymentMethodInterface
{
    /**
     * @inheritDoc
     */
    public function getCard()
    {
        return $this->getData(self::CARD);
    }

    /**
     * @inheritDoc
     */
    public function setCard($card)
    {
        $this->setData(self::CARD, $card);
    }
    public function __toArray(): array
    {
        return [
            self::CARD => $this->getCard()
        ];
    }
}
