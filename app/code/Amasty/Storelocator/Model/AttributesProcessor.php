<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model;

use Amasty\Base\Model\Serializer;

class AttributesProcessor
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        Serializer $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function process(array $attributes, int $storeId): array
    {
        $result = [];

        foreach ($attributes as $attribute) {
            $attributeId = $attribute['attribute_id'];
            if (!array_key_exists($attributeId, $result)) {
                $result[$attributeId] = [
                    'attribute_id' => $attributeId,
                    'label' => $this->getAttributeLabel($attribute, $storeId),
                    'options' => [],
                    'frontend_input' => $attribute['frontend_input'],
                    'attribute_code' => $attribute['attribute_code']
                ];
            }

            if ($attribute['frontend_input'] === 'boolean') {
                $result[$attributeId]['options'] = $this->getBooleanOptions();
            }
            if ($attribute['options_serialized']) {
                $result[$attributeId]['options'][] = $this->processOptionsSerialized($attribute, $storeId);
            }
        }

        return $result;
    }

    private function getAttributeLabel(array $attribute, int $storeId): string
    {
        $attrLabel = $attribute['frontend_label'];
        $labels = $this->serializer->unserialize($attribute['label_serialized']);
        if (isset($labels[$storeId]) && $labels[$storeId]) {
            $attrLabel = $labels[$storeId];
        }

        return $attrLabel;
    }

    private function getBooleanOptions(): array
    {
        return [
            [
                'value' => 0,
                'label' => __('No')->getText()
            ],
            [
                'value' => 1,
                'label' => __('Yes')->getText()
            ]
        ];
    }

    private function processOptionsSerialized(array $attribute, int $storeId): array
    {
        $options = $this->serializer->unserialize($attribute['options_serialized']);

        try {
            $optionLabel = $options[0];
            if (isset($options[$storeId]) && $options[$storeId]) {
                $optionLabel = $options[$storeId];
            }
        } catch (\Exception $e) {
            return [];
        }

        return [
            'value' => $attribute['value_id'],
            'label' => $optionLabel
        ];
    }
}
