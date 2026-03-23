define([
    'uiComponent',
    'jquery',
    'Magento_Checkout/js/model/quote'
], function (Component, $, quote) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Mageplaza_Osc/address-search'
        },
        initialize: function () {
            this._super();

            // Wait until DOM is ready
            $(document).ready(function () {
                var input = $('#fetchify-address');

                if (input.length && typeof window.Fetchify !== 'undefined') {
                    // Initialize Fetchify with existing accessToken
                    window.Fetchify(input[0], {
                        accessToken: '6bf9d-6c6b3-84ad7-0a89b', // your token
                        onSelect: function (address) {
                            // Auto-fill Magento shipping fields
                            var shippingAddress = quote.shippingAddress();
                            shippingAddress.firstname = address.firstName || '';
                            shippingAddress.lastname = address.lastName || '';
                            shippingAddress.street = [address.line1, address.line2].filter(Boolean);
                            shippingAddress.city = address.city;
                            shippingAddress.postcode = address.postcode;
                            shippingAddress.countryId = address.country;
                            shippingAddress.region = address.region;

                            quote.shippingAddress(shippingAddress);
                        }
                    });
                }
            });
        }
    });
});
