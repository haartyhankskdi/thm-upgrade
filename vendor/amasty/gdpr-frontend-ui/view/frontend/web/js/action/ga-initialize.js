/**
 * Initialize Google Analytics
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return {
        deferrer: {},

        initialize: function (config) {
            this.deferrer = $.Deferred();

            this.deferrer.done(function () {
                this.run(config);
            }.bind(this));
        },

        run: function (config) {
            (function (i, s, o, g, r, a, m) {
                i.GoogleAnalyticsObject = r;
                i[r] = i[r] || function () {
                    (i[r].q = i[r].q || []).push(arguments);
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m);
            }(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga'));

            // Process page info
            ga('create', config.pageTrackingData.accountId, config.cookieDomain);

            if (config.pageTrackingData.isAnonymizedIpActive) {
                ga('set', 'anonymizeIp', true);
            }

            // Process orders data
            if (config.ordersTrackingData.hasOwnProperty('currency')) {
                ga('require', 'ec', 'ec.js');

                ga('set', 'currencyCode', config.ordersTrackingData.currency);

                // Collect product data for GA
                if (config.ordersTrackingData.products) {
                    $.each(config.ordersTrackingData.products, function (index, value) {
                        ga('ec:addProduct', value);
                    });
                }

                // Collect orders data for GA
                if (config.ordersTrackingData.orders) {
                    $.each(config.ordersTrackingData.orders, function (index, value) {
                        ga('ec:setAction', 'purchase', value);
                    });
                }

                ga('send', 'pageview');
            } else {
                // Process Data if not orders
                ga('send', 'pageview' + config.pageTrackingData.optPageUrl);
            }
        }
    };
});
