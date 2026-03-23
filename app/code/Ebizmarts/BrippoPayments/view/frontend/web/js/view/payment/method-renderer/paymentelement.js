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
            stripe: null,
            elements: null,
            currentTotals: null,
            paymentRequestCustomerEmail: null,
            defaults: {
                template: 'Ebizmarts_BrippoPayments/payment/paymentelement'
            },
            getCode: function () {
                return 'brippo_payments_paymentelement';
            },
            initObservable: function () {
                this._super();
                this.isPlaceOrderActionAllowed(true);

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
            onTotalsCalculated: function () {
                setTimeout(() => {
                    this.updateRequest().then();
                }, 500);
            },
            initialize: function () {
                this._super();

                let paymentElementConfig = window.checkoutConfig.payment.brippo_payments_paymentelement;

                const selectedLogos = paymentElementConfig.payment_option_logos;

                this.logos = [];
                if (selectedLogos) {
                    const methods = selectedLogos.split(',');
                    this.logos = methods.map(function (method) {
                        return {
                            name: method.trim(),
                            cssClass: method.trim(),
                            show: true
                        };
                    });
                }

                if (!this.stripe) {
                    this.stripe = Stripe(paymentElementConfig.pKey, {
                        apiVersion: "2023-10-16"
                    });
                }

                return this;
            },
            getBusinnessName: function () {
                return window.checkoutConfig.payment.brippo_payments_paymentelement.businessName ?? ''
            },
            getLayout: function () {
                return window.checkoutConfig.payment.brippo_payments_paymentelement.layout ?? ''
            },
            doIncludeWallets: function () {
                return window.checkoutConfig.payment.brippo_payments_paymentelement.includeWallets !== '0'
            },
            isNotZeroAmountOrder: function () {
                return parseFloat(quote.totals()['grand_total']) > 0;
            },
            onRenderedHandler: async function () {
                if (!this.isNotZeroAmountOrder()) {
                    return;
                }

                const {error, paymentRequestOptions} = await this.getPaymentRequestOptions();
                if (error) {
                    this.showPaymentError(error);
                    return;
                }
                this.initializePaymentElement(paymentRequestOptions);
            },
            initializePaymentElement: function (elementsOptions) {
                this.elements = this.stripe.elements(elementsOptions);

                let paymentOptions = {
                    business: {
                        name: this.getBusinnessName()
                    },
                    layout: {
                        type: this.getLayout()
                    },
                    fields: {
                        billingDetails: {
                            address: 'if_required'
                        }
                    },
                    ...((!this.doIncludeWallets()) ? {
                        wallets: {
                            applePay: 'never',
                            googlePay: 'never'
                        }
                    } : {})
                }
                const billingAddress = quote.billingAddress();
                if (billingAddress && billingAddress.postcode && billingAddress.postcode !== '') {
                    paymentOptions.defaultValues = {
                        billingDetails: this.getBillingDetails()
                    }
                }

                const paymentElement = this.elements.create("payment", paymentOptions);
                paymentElement.mount("#stripeconnect-payment-element");
                BrippoPayments.reportAnalytic('magento2_payment_element');
            },
            placeOrder: async function () {
                if (this.validate() &&
                    additionalValidators.validate() &&
                    this.isPlaceOrderActionAllowed() === true
                ) {
                    const onPlaceOrderError = (error) => {
                        this.showPaymentError(error);
                        fullScreenLoader.stopLoader();
                        this.isPlaceOrderActionAllowed(true);
                    }

                    this.isPlaceOrderActionAllowed(false);
                    this.resetPaymentErrors();
                    fullScreenLoader.startLoader();

                    /*
                     * SUBMIT ELEMENTS
                     */
                    const elements = this.elements;
                    try {
                        const {error: submitError} = await elements.submit();
                        if (submitError) {
                            onPlaceOrderError(submitError);
                            return;
                        }
                    } catch (ex) {
                        onPlaceOrderError(ex.message);
                        this.log(ex.message, ex);
                        return;
                    }

                    /*
                     * CREATE PAYMENT METHOD
                     */
                    console.log('Brippo: Creating payment method...');
                    let paymentMethod;
                    try {
                        const {
                            error: paymentMethodError,
                            paymentMethod: paymentMethodResponse
                        } = await this.stripe.createPaymentMethod({
                            elements,
                            params: {
                                billing_details: this.getBillingDetails()
                            }
                        });
                        if (paymentMethodError) {
                            onPlaceOrderError(paymentMethodError);
                            return;
                        }
                        paymentMethod = paymentMethodResponse;
                    } catch (ex) {
                        onPlaceOrderError(ex.message);
                        this.log(ex.message, ex);
                        return;
                    }

                    /*
                     * PLACE ORDER & PI
                     */
                    console.log('Brippo: Placing order & Payment Intent...');
                    const {
                        error: placeOrderError,
                        clientSecret,
                        orderId,
                        paymentIntentId,
                        orderIncrementId
                    } = await this.placeOrderAndPaymentIntent(paymentMethod);
                    if (placeOrderError) {
                        onPlaceOrderError(placeOrderError);
                        this.log(placeOrderError, {});
                        return;
                    }

                    const {error:confirmError} = await this.confirmPayment(paymentIntentId, clientSecret,
                        paymentMethod.id, orderId);
                    if (confirmError) {
                        onPlaceOrderError(confirmError);
                        this.cancelOrder(
                            orderId,
                            orderIncrementId,
                            'Payment declined: ' + confirmError.message ?? JSON.stringify(confirmError)
                        );
                        this.log('Confirm error', confirmError);
                    }
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
            getShippingDetails: function () {
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
            async placeOrderAndPaymentIntent(paymentMethod) {
                let error, clientSecret, orderId, paymentIntentId, orderIncrementId;

                try {
                    const response = await $.ajax({
                        url: url.build('brippo_payments/paymentelement/placeorder'),
                        type: 'POST',
                        dataType: 'json',
                        contentType: 'application/json',
                        processData: false,
                        data: JSON.stringify({
                            paymentMethod,
                            billingAddress: this.getQuoteBillingAddress(),
                            shippingAddress: this.getQuoteShippingAddress(),
                            email: this.getCustomerEmail(),
                            isRecovery: false
                        }),
                        showLoader: true
                    });

                    if (response.valid === 0) {
                        error = response.message || 'Unknown error';
                    } else {
                        clientSecret     = response.client_secret;
                        orderId          = response.order_id;
                        paymentIntentId  = response.payment_intent_id;
                        orderIncrementId = response.order_increment_id;
                    }
                } catch (jqXHR) {
                    error = this.handleAjaxFail ? this.handleAjaxFail(jqXHR) : (jqXHR.responseText || jqXHR.statusText);
                    console.error('placeOrderAndPaymentIntent failed:', jqXHR);
                }

                return { error, clientSecret, orderId, paymentIntentId, orderIncrementId };
            },
            async confirmPayment(paymentIntentId, clientSecret, paymentMethodId, orderId) {
                console.log('Brippo: Confirming payment...');
                let error;
                try {
                    BrippoPayments.logOrderEvent(orderId, 'Confirming payment...');
                    const responseUrl = url.build('brippo_payments/paymentelement/response') +
                        '?orderId=' + orderId;
                    const shippingAddress = this.getShippingDetails();
                    let { error: confirmError } = await this.stripe.confirmPayment({
                        clientSecret,
                        confirmParams: {
                            payment_method: paymentMethodId,
                            return_url: responseUrl,
                            ...((shippingAddress) && {
                                shipping: shippingAddress
                            })
                        }
                    });

                    if (confirmError) {
                        BrippoPayments.logOrderEvent(orderId, 'Stripe confirm error: ' + (confirmError.message ?? JSON.stringify(confirmError)));
                        BrippoPayments.timesPaymentConfirmationFailed++;

                        let retryTimes = 9;
                        if (confirmError?.message
                            && confirmError?.message.includes('object cannot be accessed right now')) {
                            retryTimes = 0;
                        }
                        const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));
                        let paymentSucceeded = false;
                        while (retryTimes < 10) {
                            retryTimes++;
                            await sleep(1000);
                            const paymentIntent  = await this.stripe.retrievePaymentIntent(clientSecret);
                            if (paymentIntent.status === "succeeded"
                                || BrippoPayments.isDeclineCodeAllowedForRecovery(confirmError)) { //Allow to recover later
                                paymentSucceeded = true;
                                break;
                            } else {
                                error = confirmError;
                            }
                        }

                        if (paymentSucceeded) {
                            error = null;
                            window.location.href = responseUrl + "&payment_intent=" + paymentIntentId;
                        }
                    }
                } catch (ex) {
                    BrippoPayments.logOrderEvent(orderId, 'Stripe confirm exception: ' + ex.message);
                    error = ex.message;
                }

                return { error }
            },
            async getPaymentStatus(paymentIntentId) {
                let status, error;
                try {
                    await $.ajax({
                        url: url.build('brippo_payments/payments/status'),
                        data: {
                            paymentIntentId: paymentIntentId
                        },
                        type: 'POST',
                        dataType: 'json',
                        showLoader: true
                    }).fail((jqXHR) => {
                        error = this.handleAjaxFail(jqXHR);
                        console.log(error);
                    }).done(function (response) {
                        if (response.valid === 1) {
                            status = response.status;
                        }
                    });
                } catch (ex) {
                    console.log(ex.message);
                }

                return status;
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
                console.error(finalMessage);
            },
            resetPaymentErrors: function () {
                let span = document.getElementById(this.getCode() + '-payment-errors');
                if (span) {
                    span.style.display = "none";
                }
            },
            getPaymentRequestOptions: async function (maxRetries = 5) {
                if (window.checkout && window.checkout.baseUrl !== undefined) {
                    url.setBaseUrl(window.checkout.baseUrl);
                }

                let error, paymentRequestOptions;
                let attempts = 0;
                const retryDelay = 1000;

                while (attempts < maxRetries) {
                    try {
                        await $.ajax({
                            url: url.build('brippo_payments/paymentelement/paymentrequest'),
                            type: 'POST',
                            dataType: 'json',
                            showLoader: true
                        }).done((response) => {
                            paymentRequestOptions = response.options;
                            this.paymentRequestCustomerEmail = response.customerEmail;
                            attempts = maxRetries;
                        }).fail((jqXHR) => {
                            if (attempts === maxRetries - 1) {
                                error = this.handleAjaxFail(jqXHR);
                            }
                        });

                    } catch (ex) {
                        console.log(ex.message);
                        if (attempts === maxRetries - 1) {
                            error = this.handleAjaxFail(ex);
                        }
                    }

                    if (!paymentRequestOptions && attempts < maxRetries) {
                        attempts++;
                        if (attempts < maxRetries) {
                            await new Promise(resolve => setTimeout(resolve, retryDelay));
                        }
                    }
                }

                return {error, paymentRequestOptions}
            },
            log: function (message, data) {
                try {
                    $.ajax({
                        url: url.build('brippo_payments/logger/log'),
                        data: JSON.stringify({
                            'message': message,
                            'data': data
                        }),
                        type: 'POST',
                        dataType: 'json',
                        showLoader: false,
                        contentType: 'application/json',
                        processData: false,
                    }).done(function (data) {
                        console.log(data);
                    });
                } catch (ex) {
                    console.log(ex.message);
                }
            },
            updateRequest: async function () {
                if (!this.isNotZeroAmountOrder()) {
                    return;
                }

                const {error, paymentRequestOptions} = await this.getPaymentRequestOptions();
                if (error) {
                    this.showPaymentError(error);
                    return;
                }
                this.initializePaymentElement(paymentRequestOptions);
            },
            cancelOrder: function (orderId, orderIncrementId, error) {
                try {
                    $.ajax({
                        url: url.build('brippo_payments/paymentelement/cancel'),
                        data: {
                            orderId: orderId,
                            orderIncrementId: orderIncrementId,
                            error: error
                        },
                        type: 'POST',
                        dataType: 'json',
                        showLoader: true
                    }).done((result) => {
                        if (result.valid === 0) {
                            this.log('Error canceling order.', {
                                response: result
                            });
                            location.reload();
                        } else {
                            fullScreenLoader.stopLoader();
                            this.isPlaceOrderActionAllowed(true);
                        }
                    }).fail((response) => {
                        this.log('Error canceling order.', {
                            response: response
                        });
                    });
                } catch (ex) {
                    console.log(ex.message);
                }
            },
            getQuoteBillingAddress() {
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
            getQuoteShippingAddress() {
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
            handleAjaxFail: function (jqXHR) {
                let error = 'There has been an error processing your request';
                if (jqXHR && jqXHR.hasOwnProperty('error') && typeof jqXHR.error === 'function' && typeof jqXHR.error() !== "undefined") {
                    const responseText = jqXHR.error().responseText;
                    if (responseText.includes('<html')) {
                        if (jqXHR.status) {
                            error += '. Status code: ' + jqXHR.status;
                        }
                    } else {
                        error = responseText;
                    }
                }

                return error;
            },
            getCustomerEmail() {
                let email = checkoutData.getValidatedEmailValue();
                if (!email || email === '') {
                    email = this.paymentRequestCustomerEmail ?? '';
                }
                return email;
            },
            getLogos: function() {
                return this.logos.filter(function (logo) {
                    return logo.show === true;
                });
            }
        });
    }
);
