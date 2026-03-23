<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

class GiftRegistryProvider
{
    /**
     * @var GiftRegistryDataFactory
     */
    private $giftRegistryDataFactory;

    public function __construct(
        GiftRegistryDataFactory $giftRegistryDataFactory
    ) {
        $this->giftRegistryDataFactory = $giftRegistryDataFactory;
    }

    /**
     * @param int $customerId
     *
     * @return bool
     */
    public function checkGiftRegistries(int $customerId): bool
    {
        return (bool)$this->giftRegistryDataFactory->create(GiftRegistryDataFactory::GIFT_REGISTRY_ENTITY_KEY)
            ->filterByCustomerId($customerId)
            ->filterByActive()
            ->getSize();
    }

    /**
     * @param int $customerId
     *
     * @return \Magento\GiftRegistry\Model\ResourceModel\Entity\Collection
     */
    public function getGiftRegistryEntityCollectionByCustomerId(int $customerId)
    {
        /** @var \Magento\GiftRegistry\Model\ResourceModel\Entity\Collection $giftRegistryEntityCollection */
        $giftRegistryEntityCollection = $this->giftRegistryDataFactory
            ->create(GiftRegistryDataFactory::GIFT_REGISTRY_ENTITY_KEY)
            ->filterByCustomerId($customerId);

        return $giftRegistryEntityCollection;
    }

    /**
     * @param array $giftRegistryEntities
     *
     * @return \Magento\GiftRegistry\Model\ResourceModel\Person\Collection
     */
    public function getGiftRegistryPersonCollectionByEntities(array $giftRegistryEntities)
    {
        /** @var \Magento\GiftRegistry\Model\ResourceModel\Person\Collection $giftRegistryPersonCollection */
        $giftRegistryPersonCollection = $this->giftRegistryDataFactory
            ->create(GiftRegistryDataFactory::GIFT_REGISTRY_PERSON_KEY);
        $giftRegistryPersonCollection->addFieldToFilter('entity_id', ['in' => $giftRegistryEntities]);

        return $giftRegistryPersonCollection;
    }
}
