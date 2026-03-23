define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'ko',
        'brippo_payment_request_button',
        'mage/translate',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_CheckoutAgreements/js/model/agreement-validator',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-save-processor',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/checkout-data',
    ],
    function (
        $,
        Component,
        url,
        fullScreenLoader,
        ko,
        brippoExpress,
        $t,
        globalMessageList,
        quote,
        additionalValidators,
        agreementValidator,
        selectShippingAddress,
        shippingSaveProcessor,
        addressList,
        addressConverter,
        checkoutData
    ) {
        'use strict';
        return Component.extend({
            paymentRequest: null,
            defaults: {
                template: 'Ebizmarts_BrippoPayments/payment/express',
                isRendered: false
            },
            getCode: function () {
                return 'brippo_payments_express';
            },
            initObservable: function () {
                const self = this;

                this._super()
                    .observe([
                        'canMakePayment'
                    ]);

                this.shouldDisplayAtCheckoutPaymentsList = ko.computed(function () {
                    return window.checkoutConfig.payment.stripeconnect_express.enabledInCheckout &&
                        self.shouldDisplayInPaymentsList();
                }, this);

                if (!this.shouldDisplayInPaymentsList()) {
                    return this;
                }

                brippoExpress.onCanMakePaymentHandlers.push(function () {
                    $('.brippo-in-list-title').html(self.methodTitleForPaymentList());
                    $('.brippo-in-list-logo').attr('src', self.methodLogo());
                    self.canMakePayment(true);
                });

                let currentTotals = quote.totals();
                quote.totals.subscribe(function (totals) {
                    if (JSON.stringify(totals.total_segments) == JSON.stringify(currentTotals.total_segments)) {
                        return;
                    }
                        currentTotals = totals;
                        self.onTotalsCalculated();
                }, this);

                // Subscribe to changes in the billing address
                quote.billingAddress.subscribe((billingAddress) => {
                    if (billingAddress) {
                        brippoExpress.setQuoteBillingAddressForPlaceOrder(billingAddress);
                    }
                });

                // Subscribe to changes in the shipping address
                quote.shippingAddress.subscribe((shippingAddress) => {
                    if (shippingAddress) {
                        brippoExpress.setQuoteShippingAddressForPlaceOrder(shippingAddress);
                    }
                });

                document.addEventListener('brippoExpress_beforePlaceOrderEvent', function () {
                    if (quote.guestEmail) {
                        brippoExpress.setQuoteEmailForPlaceOrder(quote.guestEmail);
                    }
                    if (quote.billingAddress() && !window.brippo_quote_billing_address) {
                        brippoExpress.setQuoteBillingAddressForPlaceOrder(quote.billingAddress());
                    }
                    if (quote.shippingAddress() && !window.brippo_quote_shipping_address) {
                        brippoExpress.setQuoteShippingAddressForPlaceOrder(quote.shippingAddress());
                    }
                    if (quote && !window.brippo_quote_shipping_method) {
                        brippoExpress.setQuoteShippingMethodForPlaceOrder(quote);
                    }
                });

                return self;
            },
            onTotalsCalculated: function () {
                const self = this;
                setTimeout(function () {
                    self.initRequest();
                }, 500);
            },
            onRenderedHandler: function () {
                this.isRendered = true;
                if (this.shouldDisplayInPaymentsList()) {
                    this.initRequest();
                }
            },
            methodTitle: function () {
                return brippoExpress.getWalletName();
            },
            methodLogo: function () {
                if (brippoExpress.isGooglePay()) {
                    return window.checkoutConfig.payment.stripeconnect_express.walletsLogosUrls.googlePay;
                } else if (brippoExpress.isApplePay()) {
                    return window.checkoutConfig.payment.stripeconnect_express.walletsLogosUrls.applePay;
                } else if (brippoExpress.isLink()) {
                    return window.checkoutConfig.payment.stripeconnect_express.walletsLogosUrls.link;
                }
                return '';
            },
            useDefaultButton: function () {
                return window.checkoutConfig.payment.stripeconnect_express.checkoutButton === 'default';
            },
            methodTitleForPaymentList: function () {
                return brippoExpress.getWalletName();
            },
            initRequest: function () {
                const self = this;
                if (!window.checkoutConfig.payment.stripeconnect_express.enabledInCheckout || !this.isRendered) {
                    return;
                }

                brippoExpress.initPaymentRequest(
                    window.checkoutConfig.payment.stripeconnect_express,
                    {
                        source: 'checkout',
                        elementId: 'brippo-payments-express-button'
                    },
                    function (prButton) {
                        self.onButtonReadyHandler(prButton);
                    },
                    function (canMakePayment, paymentRequest) {
                        self.paymentRequest = paymentRequest;
                        if (canMakePayment) {
                            self.isPlaceOrderActionAllowed(true);
                        }
                        if (canMakePayment && paymentRequest) {
                            paymentRequest.on('cancel', function (ev) {
                                self.isPlaceOrderActionAllowed(true);
                            });
                        }
                    }
                );
            },
            isMageSideOSC: function() {
                return $('.ms-opc-wrapper').length > 0;
            },
            onButtonReadyHandler: function (prButton) {
                const self = this;
                prButton.on('click', function (ev) {
                    if (self.isMageSideOSC() && self.shouldDisplayInPaymentsList() && !self.useDefaultButton()) {
                        const hasCustomerAddress = addressList.some(function(address) {
                            return address.getType() === 'customer-address';
                        });

                        if (!hasCustomerAddress) {
                            let addressFlat = checkoutData.getShippingAddressFromData();
                            let address = addressConverter.formAddressDataToQuoteAddress(addressFlat);
                            brippoExpress.setQuoteShippingAddressForPlaceOrder(address);
                            selectShippingAddress(address);
                        }

                        shippingSaveProcessor.saveShippingInformation('checkout_submit').done(function () {
                            self.handleValidate(ev);
                        }).fail(function () {
                            self.showError("Unexpected error, please try again.");
                            ev.preventDefault();
                        });
                    } else {
                        self.handleValidate(ev);
                    }
                });
            },
            handleValidate: function (ev) {
                if (!this.validate()) {
                    ev.preventDefault();
                }
            },
            validate: function (region) {
                if (agreementValidator.validate() && additionalValidators.validate()) {
                    return true;
                }

                if (!agreementValidator.validate()) {
                    this.showError($t("Please agree to the terms and conditions before placing the order."));
                } else {
                    this.showError($t("Please complete all required fields before placing the order."));
                }

                return false;
            },
            showError: function (message) {
                document.getElementById('checkout').scrollIntoView(true);
                globalMessageList.addErrorMessage({ "message": message });
            },
            shouldDisplayInPaymentsList: function () {
                return window.checkoutConfig.payment.stripeconnect_express.checkoutLocation === 'payments_list';
            },
            placeOrder: function () {
                const self = this;
                if (this.validate() &&
                    additionalValidators.validate() &&
                    this.isPlaceOrderActionAllowed() === true
                ) {
                    this.isPlaceOrderActionAllowed(false);
                    self.paymentRequest.show();
                }
            }
        });
    }
);
