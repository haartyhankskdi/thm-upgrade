<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\DataCollector\Location;

use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\Import\Location;
use Amasty\Storelocator\Model\ResourceModel\Attribute;
use Amasty\Storelocator\Model\ResourceModel\Options;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class AttributeCollector implements LocationCollectorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $attributeData = [];

    /**
     * @var bool
     */
    private $loadedAttributeData = false;

    public function __construct(
        ResourceConnection $resourceConnection,
        Serializer $serializer,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
    }

    public function initialize(): void
    {
        if (!$this->loadedAttributeData) {
            $this->loadAttributeData();
        }
    }

    /**
     * @throws NoSuchEntityException
     */
    public function collect(LocationInterface $location): void
    {
        $result = [];
        if ($location->getId() && array_key_exists($location->getId(), $this->attributeData)) {
            $result = $this->prepareAttributes($this->attributeData[$location->getId()]);
        }
        $location->setAttributes($result);
    }

    private function loadAttributeData(): void
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['sa' => $this->resourceConnection->getTableName(Location::TABLE_AMASTY_STORE_ATTRIBUTE)]
            )
            ->joinLeft(
                ['attr' => $this->resourceConnection->getTableName(Attribute::TABLE_NAME)],
                '(sa.attribute_id = attr.attribute_id)'
            )
            ->joinLeft(
                ['attr_option' => $this->resourceConnection->getTableName(Options::TABLE_NAME)],
                '(sa.attribute_id = attr_option.attribute_id)',
                [
                    'options_serialized' => 'attr_option.options_serialized',
                    'value_id'           => 'attr_option.value_id'
                ]
            )
            ->where(
                'value <> ""'
            )
            ->where(
                'attr.frontend_input IN (?)',
                ['boolean', 'select', 'multiselect', 'text']
            );
        foreach ($connection->fetchAll($select) as $attributeData) {
            $this->attributeData[$attributeData['store_id']][] = $attributeData;
        }
        $this->loadedAttributeData = true;
    }

    /**
     * @param array $attributes
     *
     * @return array $result
     * @throws NoSuchEntityException
     */
    private function prepareAttributes(array $attributes): array
    {
        $result = [];

        $storeId = $this->storeManager->getStore(true)->getId();

        foreach ($attributes as $key => $attribute) {
            if (!array_key_exists($attribute['attribute_code'], $result)) {
                $result[$attribute['attribute_code']] = $attribute;
                $labels = $this->serializer->unserialize($attribute['label_serialized']);
                if (!empty($labels[$storeId])) {
                    $result[$attribute['attribute_code']]['frontend_label'] = $labels[$storeId];
                }
            }
            if (isset($attribute['options_serialized']) && $attribute['options_serialized']) {
                $values = explode(',', $attribute['value']);
                if (in_array($attribute['value_id'], $values)) {
                    $options = $this->serializer->unserialize($attribute['options_serialized']);
                    $optionTitle = '';
                    if (!empty($options[$storeId])) {
                        $optionTitle = $options[$storeId];
                    } elseif (isset($options[0])) {
                        $optionTitle = $options[0];
                    }

                    $result[$attribute['attribute_code']]['option_title'][] = $optionTitle;
                }
            }
            if ($attribute['frontend_input'] == 'boolean') {
                if ((int)$attribute['value'] == 1) {
                    $result[$attribute['attribute_code']]['option_title'] = __('Yes')->getText();
                } else {
                    $result[$attribute['attribute_code']]['option_title'] = __('No')->getText();
                }
            }

            if ($attribute['frontend_input'] == 'text') {
                $result[$attribute['attribute_code']]['option_title'] = $attribute['value'];
            }

        }

        return $result;
    }

    public function _resetState(): void
    {
        $this->loadedAttributeData = false;
    }
}
