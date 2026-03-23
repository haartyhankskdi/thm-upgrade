<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ProductPageBehavior implements OptionSourceInterface
{
    const BEHAVIOR_CLEAN_CART = 'clean_cart';
    const BEHAVIOR_MAINTAIN_CART = 'maintain_cart';
    const BEHAVIOR_MAINTAIN_CART_QTY_INCREMENTS = 'maintain_cart_qty_increments';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::BEHAVIOR_CLEAN_CART,
                'label' => __('Clean cart and checkout only that product'),
            ],
            [
                'value' => self::BEHAVIOR_MAINTAIN_CART,
                'label' => __('Add to existing cart and checkout'),
            ],
            [
                'value' => self::BEHAVIOR_MAINTAIN_CART_QTY_INCREMENTS,
                'label' => __('Add to existing cart and checkout (quantity increments)'),
            ]
        ];
    }
}
