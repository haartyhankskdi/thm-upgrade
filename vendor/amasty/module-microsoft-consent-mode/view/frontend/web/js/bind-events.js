define([
    'jquery',
    'jquery/jquery-storageapi',
], function ($) {
    'use strict';

    $('body').on('amcookie_allow amcookie_save', function () {
        if (!window.amMicrosoftConsentManager) {
            return;
        }

        const allowedGroups =
            $.cookieStorage.get(window.amMicrosoftConsentManager.gdprCookie.cookieGroupName)?.toString()?.split(',') ?? [];

        amMicrosoftConsentManager.updateConsent(window.amMicrosoftConsentManager.getConsentTypeStateByGroupIds(allowedGroups));
    });
});
