define([
    'mage/utils/wrapper',
    'jquery',
    'underscore',
    'mage/cookies',
], function (wrapper,$, _) {
    'use strict';

    return function (amfbActions) {
        amfbActions.setDataToSection = wrapper.wrapSuper(amfbActions.setDataToSection, function (code, url, eventsData) {
            if (isAllowedToRunScript()) {
                return this._super(code, url, eventsData);
            }

            return {
                done: () => {}
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

        return amfbActions;
    };
});
