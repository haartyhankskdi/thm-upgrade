define([], function () {
    'use strict';

    var data = { coupons: [] };

    return {
        setData: function (d) {
            data = d;
        },
        getCoupons: function () {
            return data.coupons || [];
        }
    };
});