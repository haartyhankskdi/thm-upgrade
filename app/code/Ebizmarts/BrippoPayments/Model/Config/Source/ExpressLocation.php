<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ExpressLocation implements OptionSourceInterface
{
    const CART = 'cart';
    const PRODUCT_PAGE = 'product_page';
    const CHECKOUT = 'checkout';
    const CHECKOUT_LIST = 'checkout_list';
    const MINICART = 'minicart';
    const RECOVER_CHECKOUT = 'recover_checkout';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::PRODUCT_PAGE,
                'label' => __('Product Page'),
            ],
            [
                'value' => self::CART,
                'label' => __('Cart'),
            ],
            [
                'value' => self::CHECKOUT,
                'label' => __('Checkout'),
            ],
            [
                'value' => self::MINICART,
                'label' => __('Minicart'),
            ]
        ];
    }
}
