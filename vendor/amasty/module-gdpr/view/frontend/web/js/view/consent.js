define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, $, Component, quote) {
        'use strict';

        var checkoutConfig = window.checkoutConfig,
            gdprConfig = checkoutConfig ? checkoutConfig.amastyGdprConsent : {};

        return Component.extend({
            defaults: {
                template: 'Amasty_Gdpr/checkout/consent'
            },

            isEnabled: gdprConfig.length !== 0,

            items: gdprConfig['consents'],

            metaInfo: gdprConfig['meta'],
            uniqId: null,

            getUniqId: function () {
                if (this.uniqId === null) {
                    this.uniqId = Math.round(Math.random() * 10000);

                    return this.uniqId;
                }
                var returnId = this.uniqId;
                this.uniqId = null;

                return returnId;
            },

            initialize: function () {
                var countries,
                    country;

                this._super();

                this.items = this.items || [];
                this.items.forEach(function (item) {
                    item.checked = ko.observable(false);
                    item.visible = ko.observable(false);
                });

                quote.billingAddress.subscribe(function (billingAddress) {
                    if (!billingAddress) {
                        return;
                    }

                    country = billingAddress.countryId;

                    if (!country) {
                        return;
                    }

                    this.items.forEach(function (item) {
                        countries = item['county_codes'] || [];
                        item.visible(countries.length ? countries.indexOf(country) !== -1 : true);
                    });
                }.bind(this));

                return this;
            },

            getWhere: function () {
                return this.metaInfo['where'];
            },

            initModal: function (element) {
                var targetCheckbox;

                $(element).find('a[href="#"]').on('mouseup', function (e) {
                    targetCheckbox = $(this)
                        .closest('div[data-role="amasty-gdpr-consent"]')
                        .find('input[type="checkbox"]');
                    e.preventDefault();
                    e.stopPropagation();
                    $('#amgdpr-privacy-popup').data(
                        'amgdpr-checkbox-selector',
                        '.payment-method._active #'
                        + $(this).closest('.amasty-gdpr-consent').find('input[type="checkbox"]').attr('id')
                    );
                });
            }
        });
    }
);
