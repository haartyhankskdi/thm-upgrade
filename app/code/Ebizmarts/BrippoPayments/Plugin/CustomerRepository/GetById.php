<?php

namespace Ebizmarts\BrippoPayments\Plugin\CustomerRepository;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Model\StripeCardFingerprintsRepository;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GetById
{
    protected $customerExtensionFactory;
    protected $stripeCardFingerprintsRepository;
    protected $logger;

    /**
     * @param CustomerExtensionInterfaceFactory $customerExtensionFactory
     * @param StripeCardFingerprintsRepository $stripeCardFingerprintsRepository
     * @param Logger $logger
     */
    public function __construct(
        CustomerExtensionInterfaceFactory $customerExtensionFactory,
        StripeCardFingerprintsRepository $stripeCardFingerprintsRepository,
        Logger $logger
    ) {
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->stripeCardFingerprintsRepository = $stripeCardFingerprintsRepository;
        $this->logger = $logger;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $resultCustomer
     * @return CustomerInterface
     */
    public function afterGetById(
        CustomerRepositoryInterface $subject,
        CustomerInterface $resultCustomer
    ) {
        try {
            $extensionAttributes = $resultCustomer->getExtensionAttributes();
            if ($extensionAttributes) {
                $customerExtension = $extensionAttributes;
            } else {
                $customerExtension = $this->customerExtensionFactory->create();
            }

            try {
                $customerStripeCardFingerprints = $this->stripeCardFingerprintsRepository->getByCustomerId(
                    $resultCustomer->getId()
                );
                if (method_exists($customerExtension, 'setStripeCardFingerprints')) {
                    $customerExtension->setStripeCardFingerprints(
                        $customerStripeCardFingerprints->getCardFingerprints()
                    );
                }
            } catch (NoSuchEntityException $e) {
                $customerExtension->setStripeCardFingerprints('');
            }

            $resultCustomer->setExtensionAttributes($customerExtension);
        } catch (Exception $e) {
            $this->logger->log($e->getMessage());
            $this->logger->log($e->getTraceAsString());
        }
        return $resultCustomer;
    }
}
