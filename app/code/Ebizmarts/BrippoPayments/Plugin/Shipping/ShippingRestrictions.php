<?php

namespace Ebizmarts\BrippoPayments\Plugin\Shipping;


use Magento\Framework\Registry;

class ShippingRestrictions {
    const MP_SHIPPINGRESTRICTION_CART = 'mp_shippingrestriction_cart';
    const MP_SHIPPINGRESTRICTION_ADDRESS = 'mp_shippingrestriction_address';

    /** @var Registry */
    protected $coreRegistry;

    public function __construct(
        Registry $registry
    ) {
        $this->coreRegistry = $registry;
    }


    public function beforeGetShippingOptions(\Ebizmarts\BrippoPayments\Helper\Express $subject)
    {
        if ($this->coreRegistry->registry(self::MP_SHIPPINGRESTRICTION_CART)) {
            $this->coreRegistry->unregister(self::MP_SHIPPINGRESTRICTION_CART);
        }
        if ($this->coreRegistry->registry(self::MP_SHIPPINGRESTRICTION_ADDRESS)) {
            $this->coreRegistry->unregister(self::MP_SHIPPINGRESTRICTION_ADDRESS);
        }
    }
}