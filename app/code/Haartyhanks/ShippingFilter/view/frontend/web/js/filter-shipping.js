define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'ko'
], function (Component, quote, ko) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();

            // Subscribe to shippingRates observable (handles AJAX update)
            quote.shippingRates.subscribe(function (rates) {
                if (!rates || rates.length === 0) return;

                var subtotal = quote.totals() ? quote.totals().base_subtotal : 0;

                var filtered = rates.filter(function (rate) {
                    if (subtotal >= 100) {
                        return rate.carrier_code === 'freeshipping';
                    } else {
                        return rate.carrier_code === 'customshipping';
                    }
                });

               if (filtered.length > 0) {

    var method = filtered[0];

    quote.shippingRates(filtered);     // update available list
    quote.shippingMethod(method);      // set selected method

    // 🔥 force backend update
    require(['Magento_Checkout/js/model/shipping-service'], function (shippingService) {
        shippingService.setShippingRates(filtered);
    });

    require(['Magento_Checkout/js/model/cart/totals-processor/default'], function (totalsProcessor) {
        totalsProcessor.estimateTotals();
    });

} else {
    quote.shippingRates([]);
    quote.shippingMethod(null);
}

            });
        }
    });
});