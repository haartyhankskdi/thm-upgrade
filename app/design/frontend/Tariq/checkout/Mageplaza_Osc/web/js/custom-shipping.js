require([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method'
], function ($, quote, selectShippingMethod) {

    const customMethod = {
        carrier_code: 'customshipping',
        method_code: 'customshipping'
    };

    const freeMethod = {
        carrier_code: 'freeshipping',
        method_code: 'freeshipping'
    };

    function filterMethods(subTotal) {
        $('.table-checkout-shipping-method tbody tr').each(function () {
            let val = $(this).find('input[type="radio"]').val();

            if (subTotal > 100) {
                // show only free shipping
                if (val === 'freeshipping_freeshipping') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            } else {
                // show only custom shipping
                if (val === 'customshipping_customshipping') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            }
        });
    }

    /**
     * Select correct method according to subtotal
     */
    function applyMethod(subTotal) {
        let method = quote.shippingMethod();

        if (subTotal > 100) {
            if (!method || method.method_code !== 'freeshipping') {
                console.log("%cSelecting Free Shipping", "color:green");
                selectShippingMethod(freeMethod);
            }
        } else {
            if (!method || method.method_code !== 'customshipping') {
                console.log("%cSelecting Custom Shipping", "color:blue");
                selectShippingMethod(customMethod);
            }
        }
    }

    /**
     * Main handler when subtotal changes
     */
    function handleTotals(totals) {
        if (!totals || typeof totals.base_subtotal === "undefined") return;

        let subTotal = parseFloat(totals.base_subtotal);

        filterMethods(subTotal);
        applyMethod(subTotal);
    }

    setTimeout(() => {
        handleTotals(quote.totals());
    }, 1500);

    quote.totals.subscribe(handleTotals);

});
