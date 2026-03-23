/**
 * initialization of facebook Pixel
 * @deprecated
 */

define([
    'jquery',
    'underscore',
    'mage/cookies'
], function ($, _) {
    'use strict';

    /**
     * @param {Object} config
     */
    return function (config) {
        let allowedCookies = [];
        let disallowedCookies = [];
        let isAllowedToRunScript = false;
        const facebookPixelCookieName = '_fbp';
        const body = $('body');

        const processFbqScriptOnAmCookieChange = () => {
            disallowedCookies = $.mage.cookies.get('amcookie_disallowed') || '';
            allowedCookies = $.mage.cookies.get('amcookie_allowed') || '';
            isAllowedToRunScript = !!allowedCookies.length
                && (!disallowedCookies || disallowedCookies.indexOf(facebookPixelCookieName) === -1)

            if (isAllowedToRunScript || !window.isGdprCookieEnabled) {
                fbq(config.callMethod, config.arguments, config.advancedInfo ?? {});
            }
        }

        processFbqScriptOnAmCookieChange();

        body.on('amcookie_save', function () {
            processFbqScriptOnAmCookieChange();
        }.bind(this));

        body.on('amcookie_allow', function () {
            processFbqScriptOnAmCookieChange();
        }.bind(this));
    };
});
