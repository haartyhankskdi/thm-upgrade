var config = {
    map: {
        '*': {
            customOptions: 'js/custom-options'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/minicart': {
                'THM_Custom/js/view/minicart-mixin': true
            }
        }
    }
};
