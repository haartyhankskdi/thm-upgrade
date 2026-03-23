<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Context;
use Mageplaza\RewardPointsPro\Model\Rule\Condition\ProductFactory as ConditionProductFactory;

/**
 * Class Combine
 * @package Mageplaza\RewardPointsPro\Model\Rule\Condition
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var ConditionProductFactory
     */
    protected $productFactory;

    /**
     * Combine constructor.
     *
     * @param Context $context
     * @param ConditionProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConditionProductFactory $productFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->productFactory = $productFactory;
        $this->setType('Mageplaza\RewardPointsPro\Model\Rule\Condition\Combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Mageplaza\RewardPointsPro\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Mageplaza\RewardPointsPro\Model\Rule\Condition\Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );

        return $conditions;
    }

    /**
     * @param array $productCollection
     *
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Product|Combine $condition */
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }
}
