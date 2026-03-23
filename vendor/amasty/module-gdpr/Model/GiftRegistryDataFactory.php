<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

/**
 * Factory class for Gift Registry Data Anonymization
 */
class GiftRegistryDataFactory
{
    public const GIFT_REGISTRY_ENTITY_KEY = 1;

    public const GIFT_REGISTRY_PERSON_KEY = 2;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Instance names to create
     *
     * @var array
     */
    protected $instanceNames = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $instanceNames
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceNames = [
            //@phpstan-ignore-next-line
            1 => \Magento\GiftRegistry\Model\ResourceModel\Entity\Collection::class,
            //@phpstan-ignore-next-line
            2 => \Magento\GiftRegistry\Model\ResourceModel\Person\Collection::class
        ]
    ) {
        $this->objectManager = $objectManager;
        $this->instanceNames = $instanceNames;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param int $key
     * @param array $data
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function create($key = 1, array $data = [])
    {
        return $this->objectManager->create($this->instanceNames[$key], $data);
    }
}
