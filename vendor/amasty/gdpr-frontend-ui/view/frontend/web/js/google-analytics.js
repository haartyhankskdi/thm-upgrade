/**
 * initialization of google analytics
 */

define([
    'jquery',
    'underscore',
    'Amasty_GdprFrontendUi/js/model/cookie-data-provider',
    'Amasty_GdprFrontendUi/js/storage/essential-cookie',
    'Amasty_GdprFrontendUi/js/action/ga-initialize',
    'mage/cookies'
], function ($, _, cookieDataProvider, essentialStorage, gaInitialize) {
    'use strict';

    /**
     * @param {Object} config
     */
    return function (config) {
        var allowServices = false,
            allowedCookies,
            allowedWebsites,
            disallowedCookieAmasty,
            allowedCookiesAmasty,
            googleAnalyticsCookieName = '_ga';

        config.cookieDomain = window.location.host;

        if (config.isCookieRestrictionModeEnabled) {
            allowedCookies = $.mage.cookies.get(config.cookieName);

            if (allowedCookies !== null) {
                allowedWebsites = JSON.parse(allowedCookies);

                if (allowedWebsites[config.currentWebsite] === 1) {
                    allowServices = true;
                }
            }
        } else {
            allowServices = true;
        }

        disallowedCookieAmasty = $.mage.cookies.get('amcookie_disallowed') || '';
        allowedCookiesAmasty = $.mage.cookies.get('amcookie_allowed') || '';
        cookieDataProvider.getCookieData().done(function (cookieData) {
            essentialStorage.update(cookieData.groupData);

            if (((!_.contains(disallowedCookieAmasty.split(','), googleAnalyticsCookieName)
                    && allowedCookiesAmasty) || !window.isGdprCookieEnabled
                || essentialStorage.isEssential(googleAnalyticsCookieName)
            ) && allowServices
            ) {
                gaInitialize.initialize(config);
                gaInitialize.deferrer.resolve();
            }
        }).fail(() => {
            if (allowServices && !window.isGdprCookieEnabled) {
                gaInitialize.initialize(config);
                gaInitialize.deferrer.resolve();
            }
        });

        if (allowServices) {
            gaInitialize.initialize(config);
        }
    };
});
