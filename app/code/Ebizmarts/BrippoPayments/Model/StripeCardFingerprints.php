<?php

namespace Ebizmarts\BrippoPayments\Model;

use Ebizmarts\BrippoPayments\Api\Data\StripeCardFingerprintsInterface;
use Magento\Framework\Model\AbstractModel;

class StripeCardFingerprints extends AbstractModel implements StripeCardFingerprintsInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\StripeCardFingerprints::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_getData(self::ID);
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->_getData(self::CUSTOMER_ID);
    }

    /**
     * @param int $customer_id
     * @return void
     */
    public function setCustomerId($customer_id)
    {
        $this->setData(self::CUSTOMER_ID, $customer_id);
    }

    /**
     * @return string
     */
    public function getCardFingerprints()
    {
        return $this->_getData(self::CARD_FINGERPRINTS);
    }

    /**
     * @param string $card_fingerprints
     * @return void
     */
    public function setCardFingerprints($card_fingerprints)
    {
        $this->setData(self::CARD_FINGERPRINTS, $card_fingerprints);
    }
}
