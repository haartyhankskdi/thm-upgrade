<?php

declare(strict_types=1);

namespace Ebizmarts\BrippoPayments\Api;

interface StripeCardFingerprintsRepositoryInterface
{
    /**
     * @param int $customerId
     * @param string $cardFingerprints
     */
    public function save(int $customerId, string $cardFingerprints);

    /**
     * @param int $customer_id
     * @return mixed
     */
    public function getByCustomerId(int $customer_id);
}
