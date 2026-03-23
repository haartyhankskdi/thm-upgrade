define([
    'jquery',
    'mage/utils/wrapper',
    'Amasty_Gdpr/js/model/consents-assigner'
], function ($, wrapper, consentsAssigner) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            consentsAssigner(paymentData);

            return originalAction(paymentData, messageContainer);
        });
    };
});
