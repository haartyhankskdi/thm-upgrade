<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Anonymization\Type;

use Amasty\Gdpr\Model\ActionLogger;
use Amasty\Gdpr\Model\Anonymization\AbstractType;
use Amasty\Gdpr\Model\CustomerData;
use Amasty\Gdpr\Model\FlagRegistry;
use Amasty\Gdpr\Model\Notification\NotificationsApplier;
use Amasty\Gdpr\Model\Notification\NotifiersProvider;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory as DeleteRequestCollectionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Data\Customer as CustomerDataModel;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Newsletter\Model\ResourceModel\Subscriber as SubscriberResource;
use Magento\Newsletter\Model\Subscriber;

class Customer extends AbstractType
{
    /**
     * @var ActionLogger
     */
    private $logger;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var NotificationsApplier
     */
    private $notificationsApplier;

    /**
     * @var DeleteRequestCollectionFactory
     */
    private $deleteRequestCollectionFactory;

    /**
     * @var SubscriberResource
     */
    private $subscriberResource;

    /**
     * @var Subscriber
     */
    private $subscriber;

    /**
     * @var FlagRegistry
     */
    private $flagRegistry;

    public function __construct(
        CollectionFactory $customerCollectionFactory,
        CustomerData $customerData,
        ActionLogger $logger,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        NotificationsApplier $notificationsApplier,
        DeleteRequestCollectionFactory $deleteRequestCollectionFactory,
        Subscriber $subscriber,
        SubscriberResource $subscriberResource,
        FlagRegistry $flagRegistry
    ) {
        parent::__construct($customerCollectionFactory, $customerData);
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->notificationsApplier = $notificationsApplier;
        $this->deleteRequestCollectionFactory = $deleteRequestCollectionFactory;
        $this->subscriber = $subscriber;
        $this->subscriberResource = $subscriberResource;
        $this->flagRegistry = $flagRegistry;
    }

    public function execute(int $customerId)
    {
        $this->deleteSubscription($customerId);
        $this->anonymizeAccountInformation($customerId);
    }

    private function anonymizeAccountInformation(int $customerId): void
    {
        $this->flagRegistry->setUpgradeOrderCustomerEmailDisabledFlag(true);
        /** @var CustomerDataModel $customer */
        $customer = $this->customerRepository->getById($customerId);
        $customerArrayData = $customer->__toArray();
        $attributeCodes = $this->customerData->getAttributeCodes('customer');

        foreach ($attributeCodes as $attributeCode) {
            switch ($attributeCode) {
                case 'email':
                    $randomString = $this->getRandomEmail();
                    break;
                case 'dob':
                    $randomString = self::ANONYMOUS_DATE;
                    break;
                case 'gender':
                    $randomString = 3; // Not Specified
                    break;
                default:
                    $randomString = $this->generateFieldValue();
            }

            if (!empty($customerArrayData[$attributeCode])) {
                $customer->setData($attributeCode, $randomString);
            }
        }

        if (!$this->isDeleting) {
            $this->notificationsApplier->apply(
                NotifiersProvider::EVENT_ANONYMIZATION,
                $customerId,
                ['anonymousEmail' => $customer->getEmail()]
            );
            $this->deleteRequestCollectionFactory->create()->approveRequest($customer->getId());
        } else {
            $this->notificationsApplier->apply(
                NotifiersProvider::EVENT_APPROVE_DELETION,
                $customerId
            );
        }

        $this->setCustomerIgnoreValidationFlag($customer);
        $this->customerRepository->save($customer);

        $addresses = $customer->getAddresses();

        /** @var AddressInterface $address */
        foreach ($addresses as $address) {
            $this->anonymizeAddress($address);
            $this->addressRepository->save($address);
        }
    }

    public function deleteSubscription(int $customerId): void
    {
        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriber->loadByCustomerId($customerId);

        if ($subscriber->getId()) {
            $subscriber->unsubscribe();
            $this->subscriberResource->delete($subscriber);
        }
    }
}
