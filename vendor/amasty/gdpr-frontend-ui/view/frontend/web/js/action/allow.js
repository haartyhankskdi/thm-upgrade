/**
 * Action Allow All Cookies
 */

define([
    'jquery',
    'mage/url',
    'Amasty_GdprFrontendUi/js/model/cookie-data-provider',
    'Amasty_GdprFrontendUi/js/model/manageable-cookie',
    'Amasty_GdprFrontendUi/js/action/ga-initialize'
], function ($, urlBuilder, cookieDataProvider, manageableCookie, gaInitialize) {
    'use strict';

    return function () {
        var url = urlBuilder.build('amcookie/cookie/allow'),
            formData = 'form_key=' + $.mage.cookies.get('form_key');

        return $.ajax({
            showLoader: true,
            method: 'POST',
            url: url,
            data: formData,
            success: function (result) {
                if (result.success === false) {
                    return;
                }

                if (gaInitialize.deferrer.resolve) {
                    gaInitialize.deferrer.resolve();
                }

                cookieDataProvider.updateCookieData().done(function (cookieData) {
                    manageableCookie.updateGroups(cookieData);
                    manageableCookie.processManageableCookies();
                }).fail(function () {
                    manageableCookie.setForce(true);
                    manageableCookie.processManageableCookies();
                });
            }
        });
    };
});
