define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/checkout-data'
    ],
    function ($, Component, url, fullScreenLoader, quote, additionalValidators, customerData, checkoutData) {
        'use strict';
        return Component.extend({
            currentTotals: null,
            paymentRequestCustomerEmail: null,
            defaults: {
                template: 'Ebizmarts_BrippoPayments/payment/payment-element-standalone'
            },
            getCode() {
                return 'brippo_payments_paymentelement_standalone';
            },
            initObservable() {
                this._super();

                this.currentTotals = quote.totals();
                quote.totals.subscribe((totals) => {
                    if (JSON.stringify(totals.total_segments) === JSON.stringify(this.currentTotals.total_segments)) {
                        return;
                    }
                    this.currentTotals = totals;
                    this.onTotalsCalculated();
                }, this);

                return this;
            },
            onTotalsCalculated() {
                setTimeout(() => {
                    this.updateRequest().then();
                }, 500);
            },
            initialize() {
                this._super();
                this.isPlaceOrderActionAllowed(false);
                return this;
            },
            waitForPaymentElementToInitialize(retry = 0) {
                setTimeout(() => {
                    if (window.brippoPaymentElement?.wasInitialized) {
                        document.addEventListener('brippoOnIntegrationReady', (event) => {
                            if (event.detail?.code === 'brippo_payments_paymentelement'
                                && event.detail?.placementId === window.brippoPaymentElement.PLACEMENT_ID.CHECKOUT_STANDALONE) {
                                this.isPlaceOrderActionAllowed(true);
                            }
                        });
                        window.brippoPaymentElement.initializePaymentElement(
                            window.brippoPaymentElement.PLACEMENT_ID.CHECKOUT_STANDALONE,
                            this.getBillingDetails()
                            ).then();
                    } else {
                        retry++;
                        console.log('Brippo standalone payment element waiting for initialization...');
                        if (retry < 100) {
                            this.waitForPaymentElementToInitialize(retry);
                        }
                    }
                }, retry === 0 ? 1 : 500);
            },
            isNotZeroAmountOrder() {
                return parseFloat(quote.totals()['grand_total']) > 0;
            },
            onRenderedHandler() {
                if (!this.isNotZeroAmountOrder()) {
                    return;
                }
                this.waitForPaymentElementToInitialize();
            },
            async placeOrder() {
                if (this.validate() &&
                    additionalValidators.validate() &&
                    this.isPlaceOrderActionAllowed() === true
                ) {
                    fullScreenLoader.startLoader();
                    this.isPlaceOrderActionAllowed(false);

                    await window.brippoPaymentElement.pay(
                        window.brippoPaymentElement.PLACEMENT_ID.CHECKOUT_STANDALONE,
                        this.getBillingDetails(),
                        this.getShippingDetails(),
                        this.getQuoteBillingAddress(),
                        this.getQuoteShippingAddress(),
                        this.getCustomerEmail(),
                        (error) => {
                            fullScreenLoader.stopLoader();
                            this.isPlaceOrderActionAllowed(true);
                        }
                    );
                }
            },
            getBillingDetails() {
                const billingAddress = quote.billingAddress();
                return {
                    email: this.getCustomerEmail(),
                    ...((billingAddress) ? {
                        name: billingAddress.firstname + (billingAddress.middlename ? ' ' + billingAddress.middlename : '') + ' ' + billingAddress.lastname,
                        address: {
                            line1: billingAddress.street && billingAddress.street.length > 0 ? billingAddress.street[0] : '',
                            line2: billingAddress.street && billingAddress.street.length > 1 ? billingAddress.street[1] : '',
                            city: billingAddress.city ?? '',
                            state: billingAddress.region ?? '',
                            postal_code: billingAddress.postcode ?? '',
                            country: billingAddress.countryId ?? ''
                        },
                        ...((billingAddress.telephone) ? {
                            phone: billingAddress.telephone
                        } : {})
                    } : {})
                };
            },
            getShippingDetails() {
                const shippingAddress = quote.shippingAddress();

                if (!shippingAddress) {
                    return null;
                }

                return {
                    name: shippingAddress.firstname + (shippingAddress.middlename ? ' ' + shippingAddress.middlename : '') + ' ' + shippingAddress.lastname,
                    address: {
                        ...((shippingAddress.street && shippingAddress.street.length > 0) ? {
                            line1: shippingAddress.street[0]
                        } : {}),
                        ...((shippingAddress.street && shippingAddress.street.length > 1) ? {
                            line2: shippingAddress.street[1]
                        } : {}),
                        ...((shippingAddress.city) ? {
                            city: shippingAddress.city
                        } : {}),
                        ...((shippingAddress.region && shippingAddress.region !== '') ? {
                            state: shippingAddress.region
                        } : {}),
                        ...((shippingAddress.postcode) ? {
                            postal_code: shippingAddress.postcode
                        } : {}),
                        ...((shippingAddress.countryId) ? {
                            country: shippingAddress.countryId
                        } : {})
                    },
                    ...((shippingAddress.telephone) ? {
                        phone: shippingAddress.telephone
                    } : {})
                };
            },
            async updateRequest() {
                if (!this.isNotZeroAmountOrder()) {
                    return;
                }

                window.brippoPaymentElement.initializePaymentElement(
                    window.brippoPaymentElement.PLACEMENT_ID.CHECKOUT_STANDALONE,
                    this.getBillingDetails()
                ).then();
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
            getCustomerEmail() {
                let email = checkoutData.getValidatedEmailValue();
                if (!email || email === '') {
                    email = this.paymentRequestCustomerEmail ?? '';
                }
                return email;
            },
            getLogoCssClass() {
                if (window.checkoutConfig.payment.brippo_payments_paymentelement_standalone?.paymentMethod) {
                    return 'payment-logo ' + window.checkoutConfig.payment.brippo_payments_paymentelement_standalone.paymentMethod;
                }
                return '';
            }
        });
    }
);
