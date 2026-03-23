define([
    'uiComponent',
    'jquery',
    'ko',
    'Haartyhanks_Checkout/js/data/coupon-data'
], function (Component, $, ko, couponData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Haartyhanks_Checkout/coupon/list'
        },

        initialize: function () {
            this._super();

            this.visible = ko.observable(true);

            this.coupons = ko.observableArray(couponData.getCoupons());

            require(['uiRegistry'], function (registry) {
                registry.set('coupon-list-component', this);
            }.bind(this));

            return this;
        },

        applyCoupon: function (code) {
            require(['Magento_SalesRule/js/view/payment/discount'], function (discountComp) {
                var instance = new discountComp();
                instance.couponCode(code);
                instance.apply();
            });
        }
    });
});
