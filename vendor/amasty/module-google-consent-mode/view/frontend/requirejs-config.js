var amasty_google_consent_mixin_enable = !!window.amConsentManager,
    config;
config = {
    config: {
        mixins: {
            'Magento_GoogleTagManager/js/google-tag-manager': {
                'Amasty_GdprFrontendUi/js/mixins/google-tag-manager-mixin': !amasty_google_consent_mixin_enable
            },
            'Magento_GoogleGtag/js/google-analytics': {
                'Amasty_GdprFrontendUi/js/mixins/google-analytics-mixin': !amasty_google_consent_mixin_enable
            },
            'Amasty_GdprFrontendUi/js/google-analytics': {
                'Amasty_GoogleConsentMode/js/mixins/google-analytics-mixin': amasty_google_consent_mixin_enable
            },
            'Amasty_GdprFrontendUi/js/model/cookie': {
                'Amasty_GoogleConsentMode/js/mixins/cookie-mixin': amasty_google_consent_mixin_enable
            },
        }
    }
}
