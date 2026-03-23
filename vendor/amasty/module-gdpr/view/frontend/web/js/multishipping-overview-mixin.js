define(['jquery'], function ($) {
    'use strict';

    var orderOverviewWidgetMixin = {
        options: {
            agreements: '.checkout-agreements, .amgdpr-checkbox.required'
        }
    };

    return function (targetWidget) {
        $.widget('mage.orderOverview', targetWidget, orderOverviewWidgetMixin);

        return $.mage.orderOverview;
    };
});
