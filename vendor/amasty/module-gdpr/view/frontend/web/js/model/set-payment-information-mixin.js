define([
    'jquery',
    'mage/utils/wrapper',
    'Amasty_Gdpr/js/model/consents-assigner'
], function ($, wrapper, consentsAssigner) {
    'use strict';

    return function (setPaymentInformation) {
        return wrapper.wrap(setPaymentInformation, function (originalAction, messageContainer, paymentData) {
            consentsAssigner(paymentData);

            return originalAction(messageContainer, paymentData);
        });
    };
});
