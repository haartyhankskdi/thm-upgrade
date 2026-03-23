/**
 * Initialize Google Tag Manager with Amasty Cookie Consent
 */
define([
    'jquery',
    'mage/utils/wrapper',
    'mage/cookies'
], function ($, wrapper) {
    'use strict';

    return function (initializeGtm) {
        return wrapper.wrap(initializeGtm, function (originalInitializeGtm, config) {
            const isGoogleAnalyticsCookieAllowed = () => {
                const disallowedCookieAmasty = $.mage.cookies.get('amcookie_disallowed') || '',
                    allowedCookiesAmasty = $.mage.cookies.get('amcookie_allowed') || '',
                    googleAnalyticsCookieName = '_ga';

                return !((disallowedCookieAmasty.split(',').includes(googleAnalyticsCookieName) ||
                    !allowedCookiesAmasty) && window.isGdprCookieEnabled);
            }

            $('body').on('amcookie_save amcookie_allow', () => {
                if (!isGoogleAnalyticsCookieAllowed()) {
                    return;
                }

                originalInitializeGtm(config);
            });

            if (!isGoogleAnalyticsCookieAllowed()) {
                return;
            }

            originalInitializeGtm(config);
        });
    };
});
