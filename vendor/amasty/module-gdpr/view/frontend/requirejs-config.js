var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Amasty_Gdpr/js/model/place-order-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'Amasty_Gdpr/js/model/set-payment-information-mixin': true
            },
            'Magento_Multishipping/js/overview': {
                'Amasty_Gdpr/js/multishipping-overview-mixin': true
            }
        }
    }
};
