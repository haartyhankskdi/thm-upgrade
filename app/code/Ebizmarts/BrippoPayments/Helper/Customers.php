<?php
namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Model\StripeCardFingerprintsRepository;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Customers extends AbstractHelper
{
    protected $logger;
    protected $customerRepository;
    protected $stripeCardFingerprintsRepository;

    public function __construct(
        Context $context,
        Logger $logger,
        CustomerRepositoryInterface $customerRepository,
        StripeCardFingerprintsRepository $stripeCardFingerprintsRepository
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->stripeCardFingerprintsRepository = $stripeCardFingerprintsRepository;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function linkCardFingerprintToCustomer($cardFingerprint, $customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $extensionAttributes = $customer->getExtensionAttributes();
            if (null !== $extensionAttributes &&
                null !== $extensionAttributes->getStripeCardFingerprints()
            ) {
                $stripeCardFingerprintsAttribute = $extensionAttributes->getStripeCardFingerprints();
                if (empty($stripeCardFingerprintsAttribute)) {
                    $previousFingerprints = $cardFingerprint;
                } else {
                    $previousFingerprints = $stripeCardFingerprintsAttribute;
                    if (strpos($previousFingerprints, $cardFingerprint) === false) {
                        $previousFingerprints .= ',' . $cardFingerprint;
                    }
                }

                $this->stripeCardFingerprintsRepository->save(
                    $customer->getId(),
                    $previousFingerprints
                );
            }
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
        }
    }
}
