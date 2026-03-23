/**
 * Cookie Data Provider Logic
 */
define([
    'jquery',
    'mage/url',
], function ($, urlBuilder) {
    'use strict';

    urlBuilder.setBaseUrl(window.BASE_URL);

    return {
        cookieData: {},
        updateRequest: null,
        cookieFetchUrl: urlBuilder.build('amcookie/cookie/cookies'),

        getCookieData: function () {
            if (Object.keys(this.cookieData).length > 0) {
                return $.Deferred().resolve(this.cookieData);
            }

            if (!this.updateRequest) {
                return this.updateCookieData();
            }

            return this.updateRequest;
        },

        updateCookieData: function () {
            this.updateRequest = $.ajax({
                url: this.cookieFetchUrl,
                type: 'GET',
                cache: true,
                dataType: 'json',
                data: {
                    allowed: $.cookie('amcookie_allowed'),
                    restriction: $.cookie('amcookie_policy_restriction')
                },
                success: function (cookieData) {
                    if (cookieData.cookiePolicy !== undefined) {
                        $.cookie('amcookie_policy_restriction', cookieData.cookiePolicy, {expires: 10, secure: true});
                    }

                    if (cookieData.cookiePolicy === 'allowed') {
                        this.cookieData = cookieData;
                        $.Deferred().resolve(this.cookieData);
                    } else {
                        $.Deferred().reject();
                    }
                }.bind(this)
            });

            return this.updateRequest;
        }
    }
});
