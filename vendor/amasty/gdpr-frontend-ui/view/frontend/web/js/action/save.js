/**
 * Action Save Cookie
 */

define([
    'jquery',
    'underscore',
    'mage/url',
    'Amasty_GdprFrontendUi/js/model/cookie-data-provider',
    'Amasty_GdprFrontendUi/js/model/cookie',
    'Amasty_GdprFrontendUi/js/model/manageable-cookie',
    'Amasty_GdprFrontendUi/js/action/ga-initialize'
], function (
    $,
    _,
    urlBuilder,
    cookieDataProvider,
    cookieModel,
    manageableCookie,
    gaInitialize
) {
    'use strict';

    var options = {
        selectors: {
            formContainer: '[data-amcookie-js="form-cookie"]',
            toggleFieldSelector: '[data-amcookie-js="field"]'
        },
        googleAnalyticsCookieName: '_ga'
    };

    return function (element, formData) {
        var url = urlBuilder.build('amcookie/cookie/savegroups'),
            disabledFields = $(options.selectors.toggleFieldSelector + ':disabled'),
            form = $(element).closest(options.selectors.formContainer);

        if (_.isUndefined(formData)) {
            disabledFields.prop('disabled', false);
            formData = form.serialize();
        }
        formData += '&form_key=' + $.mage.cookies.get('form_key');

        return $.ajax({
            showLoader: true,
            method: 'POST',
            loader: true,
            url: url,
            data: formData,
            success: function (result) {
                if (result.success === false) {
                    return;
                }
                disabledFields.attr('disabled', true);
                cookieModel.triggerSave();
                cookieDataProvider.updateCookieData().done(function (cookieData) {
                    manageableCookie.updateGroups(cookieData);
                    manageableCookie.processManageableCookies();
                }).fail(function () {
                    manageableCookie.setForce(true);
                    manageableCookie.processManageableCookies();
                });

                if (cookieModel.isCookieAllowed(options.googleAnalyticsCookieName) && gaInitialize.deferrer.resolve) {
                    gaInitialize.deferrer.resolve();
                }
            }
        });
    };
});
