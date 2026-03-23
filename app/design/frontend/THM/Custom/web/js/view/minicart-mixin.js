define([
    'jquery',
    'Magento_Checkout/js/view/minicart',
    'Magento_Customer/js/customer-data',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/url-builder'
], function (
    $,
    Component,
    customerData,
    storage,
    errorProcessor,
    urlBuilder
) {
    'use strict';

    return function (Component) {
        return Component.extend({
            initialize: function () {
                this._super();
                this.discountCode = ko.observable('');
                this.discountSuccess = ko.observable(false);
                this.discountError = ko.observable('');
                this.discountProcessing = ko.observable(false);
                return this;
            },

            applyDiscountCode: function () {
                var self = this,
                    code = this.discountCode().trim();

                if (!code) return;

                self.discountSuccess(false);
                self.discountError('');
                self.discountProcessing(true);

                var serviceUrl = urlBuilder.createUrl('/carts/mine/coupons/:couponCode', {
                    couponCode: code
                });

                storage.put(serviceUrl, false).done(function () {
                    self.discountSuccess(true);
                    self.discountError('');
                    self.discountCode('');
                    customerData.reload(['cart'], true);
                }).fail(function (response) {
                    self.discountSuccess(false);
                    self.discountError('Invalid or expired code.');
                    errorProcessor.process(response);
                }).always(function () {
                    self.discountProcessing(false);
                });
            }
        });
    };
});
