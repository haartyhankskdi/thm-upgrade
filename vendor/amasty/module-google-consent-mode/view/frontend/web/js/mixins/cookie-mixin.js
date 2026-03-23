define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    return function (cookie) {
        cookie.isCookieAllowed = wrapper.wrapSuper(cookie.isCookieAllowed, function (cookieName) {
            if (cookieName.includes('_ga')) {
                return true;
            } else {
                return this._super(cookieName);
            }
        });

        return cookie;
    };
});
