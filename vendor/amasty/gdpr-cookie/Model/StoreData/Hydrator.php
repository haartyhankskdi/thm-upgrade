<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\StoreData;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\TypeResolver;

class Hydrator
{
    /**
     * @var ScopedFieldsProvider
     */
    private $scopedFieldsProvider;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\EntityManager\Hydrator
     */
    private $hydrator;

    public function __construct(
        ScopedFieldsProvider $scopedFieldsProvider,
        TypeResolver $typeResolver,
        MetadataPool $metadataPool,
        \Magento\Framework\EntityManager\Hydrator $hydrator
    ) {
        $this->scopedFieldsProvider = $scopedFieldsProvider;
        $this->typeResolver = $typeResolver;
        $this->metadataPool = $metadataPool;
        $this->hydrator = $hydrator;
    }

    public function hydrateStoreData(\Magento\Framework\Model\AbstractModel $entity, array $entityData)
    {
        $entityType = $this->typeResolver->resolve($entity);
        $entityMetadata = $this->metadataPool->getMetadata($entityType);
        $scopedFields = $this->scopedFieldsProvider->getScopedFields($entityMetadata->getEntityTable());

        foreach ($entityData as $field => $fieldValue) {
            if ($fieldValue === null || !in_array($field, $scopedFields)) {
                unset($entityData[$field]);
            }
        }

        return $this->hydrator->hydrate($entity, $entityData);
    }
}
