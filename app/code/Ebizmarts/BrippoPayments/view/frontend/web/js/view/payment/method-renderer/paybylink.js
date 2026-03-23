define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, Component, url, fullScreenLoader, additionalValidators, customerData, quote) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Ebizmarts_BrippoPayments/payment/paybylink'
            },
            initialize: function () {
                this._super();

                let paymentConfig = window.checkoutConfig.payment.brippo_paybylink;

                this.logos = [];
                if (paymentConfig && paymentConfig.payment_option_logos) {
                    const selectedLogos = paymentConfig.payment_option_logos;
                    const methods = selectedLogos.split(',');
                    this.logos = methods.map(function (method) {
                        return {
                            name: method.trim(),
                            cssClass: method.trim(),
                            show: true
                        };
                    });
                }

                return this;
            },
            getCode: function () {
                return 'brippo_payments_paybylink';
            },
            getNote: function () {
                return window.checkoutConfig.payment.brippo_paybylink.checkoutNote;
            },
            redirectOnSuccess: function () {
                const redirectUrl = window.checkoutConfig.defaultSuccessPageUrl;
                fullScreenLoader.startLoader();
                window.location.replace(url.build(redirectUrl));
            },
            placeOrder: function () {
                if (this.validate() &&
                    additionalValidators.validate() &&
                    this.isPlaceOrderActionAllowed() === true
                ) {
                    this.isPlaceOrderActionAllowed(false);
                    this.resetPaymentErrors();

                    $.ajax({
                        url: url.build('brippo_payments/paybylink/placeorder'),
                        data: {
                            billingAddress: this.getQuoteBillingAddress(),
                            shippingAddress: this.getQuoteShippingAddress()
                        },
                        type: 'POST',
                        dataType: 'json',
                        showLoader: true
                    }).fail((response) => {
                        this.showPaymentError(response.error().responseText);
                        fullScreenLoader.stopLoader();
                        this.isPlaceOrderActionAllowed(true);
                    }).done((response) => {
                        if (response.valid === 0) {
                            this.showPaymentError(response.message);
                            fullScreenLoader.stopLoader();
                            this.isPlaceOrderActionAllowed(true);
                        } else {
                            const sections = ['cart'];
                            customerData.invalidate(sections);
                            customerData.reload(sections, true);
                            this.afterPlaceOrder();
                            this.redirectOnSuccess();
                        }
                        console.log(response);
                    });
                }
            },
            showPaymentError: function (message) {
                fullScreenLoader.stopLoader();

                let finalMessage = message;
                if (finalMessage && typeof finalMessage === 'object' && finalMessage.message) {
                    finalMessage = finalMessage.message;
                }

                let span = document.getElementById(this.getCode() + '-payment-errors');
                if (span) {
                    span.innerHTML = '<span>' + finalMessage + '</span>';
                    span.style.display = "block";
                }
            },
            resetPaymentErrors: function () {
                let span = document.getElementById(this.getCode() + '-payment-errors');
                if (span) {
                    span.style.display = "none";
                }
            },
            log: function (message, data) {
                $.ajax({
                    url: url.build('brippo_payments/logger/log'),
                    data: {
                        'message': message,
                        'data': data
                    },
                    type: 'POST',
                    dataType: 'json',
                    showLoader: false
                }).done(function (data) {
                    console.log(data);
                });
            },
            getQuoteBillingAddress: function () {
                const billingAddress = quote.billingAddress();
                if (!billingAddress) {
                    return {}
                }

                return {
                    firstname: billingAddress.firstname,
                    lastname: billingAddress.lastname,
                    street: billingAddress.street,
                    city: billingAddress.city,
                    countryId: billingAddress.countryId,
                    postcode: billingAddress.postcode,
                    telephone: billingAddress.telephone,
                    region: billingAddress.region,
                    regionId: billingAddress.regionId
                }
            },
            getQuoteShippingAddress: function () {
                const shippingAddress = quote.shippingAddress();
                if (!shippingAddress) {
                    return {}
                }

                return {
                    firstname: shippingAddress.firstname,
                    lastname: shippingAddress.lastname,
                    street: shippingAddress.street,
                    city: shippingAddress.city,
                    countryId: shippingAddress.countryId,
                    postcode: shippingAddress.postcode,
                    telephone: shippingAddress.telephone,
                    region: shippingAddress.region,
                    regionId: shippingAddress.regionId
                }
            },
            getLogos: function() {
                return this.logos.filter(function (logo) {
                    return logo.show === true;
                });
            }
        });
    }
);
