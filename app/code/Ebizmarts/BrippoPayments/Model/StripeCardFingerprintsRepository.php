<?php
declare(strict_types=1);

namespace Ebizmarts\BrippoPayments\Model;

use Ebizmarts\BrippoPayments\Api\StripeCardFingerprintsRepositoryInterface;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Model\StripeCardFingerprintsFactory;
use Ebizmarts\BrippoPayments\Model\ResourceModel\StripeCardFingerprints as StripeCardFingerprintsResource;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;

class StripeCardFingerprintsRepository implements StripeCardFingerprintsRepositoryInterface
{
    protected $stripeCardFingerprintsFactory;
    protected $stripeCardFingerprintsResource;
    protected $logger;

    public function __construct(
        StripeCardFingerprintsFactory $stripeCardFingerprintsFactory,
        StripeCardFingerprintsResource $stripeCardFingerprintsResource,
        Logger $logger
    ) {
        $this->stripeCardFingerprintsFactory = $stripeCardFingerprintsFactory;
        $this->stripeCardFingerprintsResource = $stripeCardFingerprintsResource;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * @throws AlreadyExistsException
     */
    public function save(int $customerId, string $cardFingerprints)
    {
        try {
            $stripeCardFingerprints = $this->getByCustomerId($customerId);
            $stripeCardFingerprints->setCardFingerprints($cardFingerprints);
        } catch (NoSuchEntityException $ex) {
            $stripeCardFingerprints = $this->stripeCardFingerprintsFactory->create();
            $stripeCardFingerprints->setCustomerId($customerId);
            $stripeCardFingerprints->setCardFingerprints($cardFingerprints);
        }
        return $this->stripeCardFingerprintsResource->save($stripeCardFingerprints);
    }

    /**
     * @param int $customer_id
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getByCustomerId(int $customer_id)
    {
        $stripeCardFingerprints = $this->stripeCardFingerprintsFactory->create();
        $this->stripeCardFingerprintsResource->load($stripeCardFingerprints, $customer_id, 'customer_id');
        if (!isset($stripeCardFingerprints['customer_id'])) {
            throw new NoSuchEntityException(
                __('Stripe Card Fingerprints not found for customer #' . $customer_id . '.')
            );
        }
        return $stripeCardFingerprints;
    }
}
