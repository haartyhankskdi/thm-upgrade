define([
    'jquery'
], function ($) {
    'use strict';

    return function (paymentData) {
        var consents = checkoutConfig.amastyGdprConsent.consents || [];
        var consentData = {};

        _.each(consents, function (consent) {
            var consentElement = $('input[data-gdpr-checkbox-code="' + consent.checkbox_code + '"]:visible');

            if (consentElement) {
                consentData[consent.checkbox_code] = Boolean(consentElement.prop('checked'));
            }
        });

        if (!paymentData['additional_data']) {
            paymentData['additional_data'] = {};
        }

        paymentData['additional_data']['amgdpr_agreement'] = JSON.stringify(consentData);
    };
});
