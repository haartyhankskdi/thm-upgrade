define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'brippo_payments_paymentelement',
                component: 'Ebizmarts_BrippoPayments/js/view/payment/method-renderer/paymentelement'
            },
            {
                type: 'brippo_payments_express',
                component: 'Ebizmarts_BrippoPayments/js/view/payment/method-renderer/express'
            },
            {
                type: 'brippo_payments_paybylink',
                component: 'Ebizmarts_BrippoPayments/js/view/payment/method-renderer/paybylink'
            },
            {
                type: 'brippo_payments_paymentelement_standalone',
                component: 'Ebizmarts_BrippoPayments/js/view/payment/method-renderer/payment-element-standalone'
            },
            {
                type: 'brippo_payments_ece',
                component: 'Ebizmarts_BrippoPayments/js/view/payment/method-renderer/express-checkout-element-list'
            }
        );
        return Component.extend({});
    }
);
