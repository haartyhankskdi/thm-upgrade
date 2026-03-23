<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Anonymization\Type;

use Amasty\Base\Model\Serializer;
use Amasty\Gdpr\Model\Anonymization\AbstractType;
use Amasty\Gdpr\Model\CustomerData;
use Amasty\Gdpr\Model\GiftRegistryDataFactory;
use Amasty\Gdpr\Model\GiftRegistryProvider;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DataObjectFactory;
use Magento\GiftRegistry\Model\Entity;
use Magento\GiftRegistry\Model\Person;

class GiftRegistry extends AbstractType
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var GiftRegistryProvider
     */
    private $giftRegistryProvider;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        CollectionFactory $customerCollectionFactory,
        CustomerData $customerData,
        DataObjectFactory $dataObjectFactory,
        ProductMetadataInterface $productMetadata,
        GiftRegistryProvider $giftRegistryProvider,
        ?Serializer $serializer = null // TODO move to not optional
    ) {
        parent::__construct($customerCollectionFactory, $customerData);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->productMetadata = $productMetadata;
        $this->giftRegistryProvider = $giftRegistryProvider;
        $this->serializer = $serializer ?? ObjectManager::getInstance()->get(Serializer::class);
    }

    public function execute(int $customerId)
    {
        if ($this->productMetadata->getEdition() === 'Enterprise') {
            /** @var \Magento\GiftRegistry\Model\ResourceModel\Entity\Collection $giftRegistryEntityCollection */
            $giftRegistryEntityCollection = $this->giftRegistryProvider
                ->getGiftRegistryEntityCollectionByCustomerId($customerId);
            $giftRegistryEntities = [];

            foreach ($giftRegistryEntityCollection->getItems() as $giftRegistry) {
                $this->anonymizeGiftRegistryEntity($giftRegistry);
                $giftRegistry->save();

                $giftRegistryEntities[] = $giftRegistry->getEntityId();
            }

            if (!empty($giftRegistryEntities)) {
                /** @var \Magento\GiftRegistry\Model\ResourceModel\Person\Collection $giftRegistryPersonCollection */
                $giftRegistryPersonCollection = $this->giftRegistryProvider
                    ->getGiftRegistryPersonCollectionByEntities($giftRegistryEntities);

                foreach ($giftRegistryPersonCollection->getItems() as $giftRegistryPerson) {
                    $this->anonymizeGiftRegistryPerson($giftRegistryPerson);
                    $giftRegistryPerson->save();
                }
            }
        }
    }

    /**
     * @phpstan-ignore-next-line
     */
    private function anonymizeGiftRegistryEntity(Entity $giftRegistry): void
    {
        $giftRegistryAttributeCodes = $this->customerData->getAttributeCodes('gift_registry_entity');

        foreach ($giftRegistryAttributeCodes as $code) {
            switch ($code) {
                case 'shipping_address':
                    $addressArray = $this->serializer->unserialize($giftRegistry->getShippingAddress());

                    if (!$addressArray
                        || ($addressArray['country_id'] == self::ANONYMIZE_COUNTRY_ID
                            && $addressArray['region_id'] == self::ANONYMIZE_REGION_ID)
                    ) {
                        continue 2;
                    }

                    $address = $this->dataObjectFactory->create()
                        ->addData($addressArray);
                    $this->anonymizeAddress($address);

                    $randomString = $this->serializer->serialize($address->getData());
                    break;
                case 'custom_values':
                    $randomString = null;
                    break;
                case 'event_country':
                    $randomString = "00";
                    break;
                case 'event_date':
                    $randomString = self::ANONYMOUS_DATE;
                    break;
                default:
                    $randomString = $this->generateFieldValue();
            }

            if ($giftRegistry->getData($code)) {
                $giftRegistry->setData($code, $randomString);
            }
        }
    }

    /**
     * @phpstan-ignore-next-line
     */
    private function anonymizeGiftRegistryPerson(Person $person): void
    {
        $attributeCodes = $this->customerData->getAttributeCodes('gift_registry_person');

        foreach ($attributeCodes as $code) {
            switch ($code) {
                case 'email':
                    $randomString = $this->getRandomEmail();
                    break;
                case 'role':
                    $randomString = null;
                    break;
                case 'custom_values':
                    $randomString = 'null';
                    break;
                default:
                    $randomString = $this->generateFieldValue();
            }

            if ($person->getData($code)) {
                $person->setData($code, $randomString);
            }
        }
    }
}
