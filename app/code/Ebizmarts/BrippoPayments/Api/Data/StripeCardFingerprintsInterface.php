<?php
declare(strict_types=1);

namespace Ebizmarts\BrippoPayments\Api\Data;

interface StripeCardFingerprintsInterface
{
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const CARD_FINGERPRINTS = 'card_fingerprints';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customer_id
     * @return void
     */
    public function setCustomerId($customer_id);

    /**
     * @return string
     */
    public function getCardFingerprints();

    /**
     * @param string $card_fingerprints
     * @return void
     */
    public function setCardFingerprints($card_fingerprints);
}
