var config = {
    map: {
        '*': {
            'Magento_GoogleAnalytics/js/google-analytics': 'Amasty_GdprFrontendUi/js/google-analytics'
        }
    },
    config: {
        mixins: {
            'Magento_GoogleTagManager/js/google-tag-manager': {
                'Amasty_GdprFrontendUi/js/mixins/google-tag-manager-mixin': true
            },
            'Magento_GoogleGtag/js/google-analytics': {
                'Amasty_GdprFrontendUi/js/mixins/google-analytics-mixin': true
            },
            'Magento_Catalog/js/product/storage/ids-storage': {
                'Amasty_GdprFrontendUi/js/mixins/ids-storage-mixin': true
            },
            'Magento_Customer/js/customer-data': {
                'Amasty_GdprFrontendUi/js/mixins/customer-data-mixin': true
            },
            'Magento_Theme/js/view/messages': {
                'Amasty_GdprFrontendUi/js/mixins/disposable-customer-data-mixin': true
            },
            'Magento_Review/js/view/review': {
                'Amasty_GdprFrontendUi/js/mixins/disposable-customer-data-mixin': true
            },
            'Amasty_FacebookPixel/js/fbq-functions': {
                'Amasty_GdprFrontendUi/js/mixins/facebook/fbq-functions-mixin': true
            },
            'Amasty_FacebookPixelAdvancedMatching/js/fbq-functions': {
                'Amasty_GdprFrontendUi/js/mixins/facebook/fbq-functions-mixin': true
            },
            'Amasty_FacebookPixel/js/action/amfb-actions': {
                'Amasty_GdprFrontendUi/js/mixins/facebook/amfb-actions-mixin': true
            },
            'Amasty_FacebookPixel/js/amfb-init': {
                'Amasty_GdprFrontendUi/js/mixins/facebook/amfb-init-mixin': true
            },
        }
    },
    shim: {
        'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver': {
            deps: ['Amasty_GdprFrontendUi/js/mixins/customer-data-mixin']
        }
    }
};
