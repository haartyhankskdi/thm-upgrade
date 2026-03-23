define([
    'mage/utils/wrapper',
    'jquery',
    'underscore',
    'mage/cookies',
], function (wrapper,$, _) {
    'use strict';

    return function (fbqFunctions) {
        fbqFunctions.initPixels = wrapper.wrapSuper(fbqFunctions.initPixels, function (pixelIds) {
            if (isAllowedToRunScript()) {
                this._super(pixelIds);
            }
        });

        /**
         * @returns {boolean}
         */
        function isAllowedToRunScript() {
            if (!window.isGdprCookieEnabled) {
                return true;
            }

            const facebookPixelCookieName = '_fbp';
            const allowedCookies = $.mage.cookies.get('amcookie_allowed') || '';
            const disallowedCookies = $.mage.cookies.get('amcookie_disallowed') || '';

            return !!allowedCookies.length
                && (!disallowedCookies || disallowedCookies.indexOf(facebookPixelCookieName) === -1);
        }

        return fbqFunctions;
    };
});
