define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Amasty_Gdpr/js/model/consent-validator'
    ],
    function (Component, additionalValidators, agreementValidator) {
        'use strict';
        additionalValidators.registerValidator(agreementValidator);
        return Component.extend({});
    }
);
