define([
    'jquery',
    'jquery/jquery-storageapi',
], function ($) {
    'use strict';

    $('body').on('amcookie_allow amcookie_save', function () {
        if (!window.amConsentManager) {
            return;
        }

        const allowedGroups =
            $.cookieStorage.get(window.amConsentManager.gdprCookie.cookieGroupName)?.toString()?.split(',') ?? [];

        amConsentManager.updateConsent(window.amConsentManager.getConsentTypeStateByGroupIds(allowedGroups));
    });
});
