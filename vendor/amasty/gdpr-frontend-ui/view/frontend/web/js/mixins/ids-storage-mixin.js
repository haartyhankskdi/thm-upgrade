define([
    'jquery',
    'mage/utils/wrapper',
    'Amasty_GdprFrontendUi/js/model/cookie',
    'Amasty_GdprFrontendUi/js/model/cookie-data-provider'
], function ($, wrapper, cookies, cookieDataProvider) {
    'use strict';

    return function (idsStorage) {
        idsStorage.initLocalStorage = wrapper.wrapSuper(idsStorage.initLocalStorage, function () {
            if (window.cookieStorage.amCookieObserved === true) {
                this._super();
                return this;
            }

            let isCookieAllowed = true;
            const cookieSetItem = window.cookieStorage.setItem.bind(window.cookieStorage);

            window.cookieStorage.setItem = (name, value, options) => {
                cookieDataProvider.getCookieData().done(() => {
                    isCookieAllowed = cookies.isCookieAllowed(name);

                    if (isCookieAllowed || !window.isGdprCookieEnabled) {
                        cookieSetItem(name, value, options);
                    }
                });
            };

            window.cookieStorage.amCookieObserved = true;
            this._super();
            return this;
        });

        return idsStorage;
    };
});
