define(
    [
        'jquery',
        'knockout',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/checkout-data'
    ],
    function ($, ko, Component, url, fullScreenLoader, quote, additionalValidators, customerData, checkoutData) {
        'use strict';
        return Component.extend({
            currentTotals: null,
            paymentRequestCustomerEmail: null,
            defaults: {
                template: 'Ebizmarts_BrippoPayments/payment/express-checkout-element-list'
            },
            title: 'Wallet',
            getCode() {
                return 'brippo_payments_ece';
            },
            getTitle() {
                return this.title;
            },
            initObservable() {
                this._super()
                    .observe([
                        'isWalletAvailable'
                    ]);

                this.shouldDisplayAtCheckoutPaymentsList = ko.computed(() => {
                    return window.checkoutConfig.payment.brippo_payments_ece.enabledInPaymentList === '1';
                }, this);

                if (!window.checkoutConfig.payment.brippo_payments_ece.enabledInPaymentList) {
                    return this;
                }

                document.addEventListener('brippoOnIntegrationReady', (event) => {
                    if (event.detail?.code === 'brippo_payments_ece'
                        && event.detail?.placementId === window.brippoExpressCheckoutElement.PLACEMENT_ID.CHECKOUT_LIST) {
                        this.setupLogos(event.detail.availablePaymentMethods);
                        this.setupTitle(event.detail.availablePaymentMethods);
                        this.isWalletAvailable(true);
                    }
                });

                this.currentTotals = quote.totals();
                quote.totals.subscribe((totals) => {
                    if (JSON.stringify(totals.total_segments) === JSON.stringify(this.currentTotals.total_segments)) {
                        return;
                    }
                    this.currentTotals = totals;
                    this.onTotalsCalculated();
                }, this);

                // Subscribe to changes in the billing address
                quote.billingAddress.subscribe((billingAddress) => {
                    if (billingAddress) {
                        window.brippoExpressCheckoutElement.setCheckoutBillingAddress(billingAddress);
                    }
                });

                // Subscribe to changes in the shipping address
                quote.shippingAddress.subscribe((shippingAddress) => {
                    if (shippingAddress) {
                        window.brippoExpressCheckoutElement.setCheckoutShippingAddress(shippingAddress, this.getShippingDetails());
                    }
                });

                document.addEventListener('brippoExpressCheckoutElement_beforePlaceOrder', () => {
                    if (quote?.guestEmail) {
                        window.brippoExpressCheckoutElement.setCheckoutEmail(quote.guestEmail);
                    }
                    if (quote?.billingAddress()) {
                        window.brippoExpressCheckoutElement.setCheckoutBillingAddress(quote.billingAddress());
                    }
                    if (quote?.shippingAddress()) {
                        window.brippoExpressCheckoutElement.setCheckoutShippingAddress(quote.shippingAddress(), this.getShippingDetails());
                    }
                    if (quote) {
                        window.brippoExpressCheckoutElement.setCheckoutShippingMethod(quote);
                    }
                });

                return this;
            },
            setupLogos(availablePaymentMethods) {
                const logosLabel = document.querySelector('.brippoExpressCheckoutElementList label .logos');
                if (availablePaymentMethods) {
                    Object.entries(availablePaymentMethods).forEach(([key, value]) => {
                        if (value === true) {
                            const logo = document.createElement('span');
                            logo.classList.add(key.toLowerCase());
                            logo.classList.add('payment-logo');
                            logosLabel.appendChild(logo);
                        }
                    });
                }
            },
            setupTitle(availablePaymentMethods) {
                const titleLabel = document.querySelector('.brippoExpressCheckoutElementList label .title');
                if (availablePaymentMethods) {
                    let title = ''
                    let pmsEnabled = []
                    Object.entries(availablePaymentMethods).forEach(([key, value]) => {
                        if (value === true) {
                            pmsEnabled.push(key);
                        }
                    });

                    for (let i = 0; i < pmsEnabled.length; i++) {
                        let pmName;
                        switch (pmsEnabled[i]) {
                            case 'applePay':
                                pmName = 'Apple Pay';
                                break;
                            case 'googlePay':
                                pmName = 'Google Pay';
                                break;
                            case 'link':
                                pmName = 'Link';
                                break;
                            default:
                                pmName = pmsEnabled[i];
                                break;
                        }

                        if (i === 0) {
                            title = pmName;
                        } else if (i === pmsEnabled.length - 1) {
                            title += ' or ' + pmName;
                        } else {
                            title += ', ' + pmName;
                        }
                    }

                    titleLabel.innerHTML = title;
                    this.title = title;
                }
            },
            onTotalsCalculated() {
                setTimeout(() => {
                    this.updateRequest().then();
                }, 500);
            },
            waitForECEToInitialize(retry = 0) {
                setTimeout(() => {
                    if (window.brippoExpressCheckoutElement?.wasInitialized) {
                        window.brippoExpressCheckoutElement.initializeExpressCheckout(
                            window.brippoExpressCheckoutElement.PLACEMENT_ID.CHECKOUT_LIST
                            ).then();
                    } else {
                        retry++;
                        console.log('Brippo express checkout element list waiting for initialization...');
                        if (retry < 100) {
                            this.waitForECEToInitialize(retry);
                        }
                    }
                }, retry === 0 ? 1 : 500);
            },
            isNotZeroAmountOrder() {
                return parseFloat(quote.totals()['grand_total']) > 0;
            },
            onRenderedHandler() {
                if (!this.isNotZeroAmountOrder()
                    || !window.checkoutConfig.payment.brippo_payments_ece.enabledInPaymentList) {
                    return;
                }
                this.waitForECEToInitialize();
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

                window.brippoExpressCheckoutElement.initializeExpressCheckout(
                    window.brippoExpressCheckoutElement.PLACEMENT_ID.CHECKOUT_LIST
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
        });
    }
);
