define([
    'jquery',
    'Haartyhanks_Checkout/js/data/coupon-data'
], function ($, couponData) {
    'use strict';

    $.get('/hhcheckout/coupon/index', function (response) {
        couponData.setData({ coupons: response });
        console.log("coupon-init js file load :", response);
    });

    return {};
});
