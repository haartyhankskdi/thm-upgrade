define([
    'jquery',
    'jquery/jquery-storageapi',
], function ($) {
    'use strict';

    $('body').on('amcookie_allow amcookie_save', function () {
        if (!window.amClarityConsentManager) {
            return;
        }

        const allowedGroups =
            $.cookieStorage.get(window.amClarityConsentManager?.gdprCookie?.cookieGroupName)?.toString()?.split(',') ?? [];

        amClarityConsentManager.updateConsent(window.amClarityConsentManager.getConsentTypeStateByGroupIds(allowedGroups));
    });
});
