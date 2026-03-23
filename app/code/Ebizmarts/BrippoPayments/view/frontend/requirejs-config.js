
let config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/cart/totals-processor/default': {
                'Ebizmarts_BrippoPayments/js/view/cart/estimate-shipping-mixin': true
            }
        }
    },
    map: {
        '*': {
            brippo_payment_request_button: 'Ebizmarts_BrippoPayments/js/view/payment/express-checkout',
            brippo_failsafe_paymentelement: 'Ebizmarts_BrippoPayments/js/view/payment/failsafe-paymentelement',
            brippo_refresh_cart: 'Ebizmarts_BrippoPayments/js/view/order/success'
        }
    }
};
