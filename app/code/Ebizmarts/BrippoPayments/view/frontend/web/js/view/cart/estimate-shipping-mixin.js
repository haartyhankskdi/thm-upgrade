define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    return function (shippingTotalsProcessor) {
        shippingTotalsProcessor.estimateTotals = wrapper.wrapSuper(
            shippingTotalsProcessor.estimateTotals,
            function (address) {
                let updateCallback;

                /**
                 * Update payment request shipping option and address
                 */
                updateCallback = function () {
                    window.dispatchEvent(new Event("brippo_shipping_estimate"));
                };
                return this._super(address).done(updateCallback);
            }
        );

        return shippingTotalsProcessor;
    };
});
