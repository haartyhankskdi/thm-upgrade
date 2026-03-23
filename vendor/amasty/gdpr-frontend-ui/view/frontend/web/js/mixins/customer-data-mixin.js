define([
    'jquery',
    'mage/utils/wrapper',
    'Amasty_GdprFrontendUi/js/action/cookie-setter',
    'jquery/jquery-storageapi'
], function ($, wrapper, cookieSetter) {
    'use strict';

    return function (customerData) {
        customerData.init = wrapper.wrapSuper(customerData.init, function () {
            $.cookieStorage.set = cookieSetter($.cookieStorage.set, $.cookieStorage);
            this._super();
        });

        return customerData;
    };
});
