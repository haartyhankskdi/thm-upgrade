
window.brippoPaymentElement = {
    PLACEMENT_ID: {
        CHECKOUT: 'checkout',
        CHECKOUT_STANDALONE: 'checkout_standalone',
        RECOVER_CHECKOUT: 'recover_checkout'
    },
    wasInitialized: false,
    lastTotalRequestedByPlacement: {},
    paymentRequestByPlacement: {},
    elementsByPlacement: {},
    customerEmail: null,
    placementsSubscribedToCartUpdates: [],
    async initialize() {
        BrippoPayments.initialize().then();
        if (BrippoPayments.config) {
            this.onBrippoPaymentsInitialized();
        }
    },
    onBrippoPaymentsInitialized() {
        if (this.wasInitialized) {
            return;
        }
        this.wasInitialized = true;

        if (BrippoPayments.isRecoverCheckout()
            && typeof brippoRecoverIsStockAvailable !== 'undefined'
            && !brippoRecoverIsStockAvailable) {
            return;
        }

        if (BrippoPayments.isRecoverCheckout()) {
            this.initializePaymentElement(this.PLACEMENT_ID.RECOVER_CHECKOUT).then();
        }
    },
    async initializePaymentElement(placementId, billingDetails) {
        if (!this.wasInitialized) {
            return;
        } else if (placementId === this.PLACEMENT_ID.RECOVER_CHECKOUT
            && typeof brippoRecoverIsStockAvailable !== 'undefined'
            && !brippoRecoverIsStockAvailable) {
            return;
        }

        if (!(this.placementsSubscribedToCartUpdates.includes(placementId))) {
            this.placementsSubscribedToCartUpdates.push(placementId);
        }

        const {error: paymentRequestOptionsError, paymentRequestOptions, customerEmail} = await this.getPaymentRequestOptions(placementId);

        if (paymentRequestOptionsError) {
            this.showPaymentError(paymentRequestOptionsError, placementId);
            return;
        }

        this.customerEmail = customerEmail;
        let amount = parseFloat(paymentRequestOptions.amount);

        //Disable for 0 amount
        if (amount <= 0) {
            console.log('Payment Element [' + placementId + '] Brippo won’t create payment request for 0');
            const buttonPay = this.getButtonPay(placementId);
            if (buttonPay) {
                buttonPay.style.display = 'none';
            }
            return;
        }

        // Avoid re-requesting for the same amount
        if (placementId in this.lastTotalRequestedByPlacement &&
            this.lastTotalRequestedByPlacement[placementId] === paymentRequestOptions.amount) {
            return;
        }
        this.lastTotalRequestedByPlacement[placementId] = paymentRequestOptions.amount;
        this.paymentRequestByPlacement[placementId] = {
            paymentRequestOptions: paymentRequestOptions
        }

        this.elementsByPlacement[placementId] = BrippoPayments.stripe.elements(paymentRequestOptions);

        let paymentOptions = {
            business: {
                name: BrippoPayments.config.paymentElement.businessName
            },
            layout: {
                type: BrippoPayments.config.paymentElement.layout
            },
            fields: {
                billingDetails: {
                    address: 'if_required'
                }
            },
            ...((placementId === this.PLACEMENT_ID.RECOVER_CHECKOUT
                || placementId === this.PLACEMENT_ID.CHECKOUT_STANDALONE) && {
                wallets: {
                    applePay: 'never',
                    googlePay: 'never'
                }
            })
        }

        if (billingDetails?.address?.postal_code && billingDetails.address.postal_code !== '') {
            paymentOptions.defaultValues = {
                billingDetails: billingDetails
            }
        }

        const paymentElement = this.elementsByPlacement[placementId].create("payment", paymentOptions);

        paymentElement.on('ready', (event) => {
            document.dispatchEvent(new CustomEvent('brippoOnIntegrationReady', {
                detail: {
                    code: 'brippo_payments_paymentelement',
                    placementId: placementId
                }
            }));
        });

        paymentElement.on('loaderror', (event) => {
            this.showPaymentError(event.error.message, placementId);
        });

        BrippoPayments.waitForElementToExists(
            '#' + this.getElementUniqueId('mount', placementId),
            () =>
            {
                paymentElement.mount('#' + this.getElementUniqueId('mount', placementId));
                const buttonPay = this.getButtonPay(placementId);
                if (buttonPay) {
                    buttonPay.style.display = 'block';
                }
            }
        )

        BrippoPayments.reportAnalytic('magento2_payment_element_' + placementId);
    },
    async getPaymentRequestOptions(placementId) {
        this.resetPaymentErrors(placementId);
        let error, paymentRequestOptions, customerEmail;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.paymentElement.controllers.paymentRequest);
        await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                placementId: placementId,
                ...((placementId === this.PLACEMENT_ID.RECOVER_CHECKOUT) && {
                    recoverOrderId: brippoRecoverOrderEntityId
                })
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText + ' (Status code ' + response.status + ')');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.valid === 1) {
                    console.log(`[${placementId}] Brippo Payment Element payment request completed for ${
                        data.options.amount}.`);
                    paymentRequestOptions = data.options;
                    customerEmail = data.customerEmail;
                } else {
                    error = data.message || "An error occurred while processing the payment request.";
                }
            })
            .catch(err => {
                error = BrippoPayments.prettifyErrorMessage(err.message);
                console.error('There was a problem while fetching payment element request:', error);
            });

        return {error, paymentRequestOptions, customerEmail};
    },
    getElementUniqueId(type, placementId) {
        const uniqueId = 'brippoPaymentElement_' + placementId;
        return uniqueId + '_' + type;
    },
    showPaymentError(message, placementId) {
        let span = document.getElementById(this.getElementUniqueId('error', placementId));
        if (span) {
            span.innerHTML = '<span>' + message + '</span>';
            span.style.display = "block";
        }
    },
    resetPaymentErrors(placementId) {
        let span = document.getElementById(this.getElementUniqueId('error', placementId));
        if (span) {
            span.style.display = "none";
        }
    },
    getButtonPay(placementId) {
        switch (placementId) {
            case this.PLACEMENT_ID.RECOVER_CHECKOUT:
                return document.getElementById('brippoRecoverPaymentElementPay');
            case this.PLACEMENT_ID.CHECKOUT_STANDALONE:
                return document.getElementById('brippoPaymentElementStandalonePay');
        }
        return null;
    },
    async pay(placementId, billingDetails, shippingDetails, checkoutBillingAddress,
              checkoutShippingAddress, checkoutCustomerEmail, errorHandler) {
        this.resetPaymentErrors(placementId);
        BrippoPayments.showVeilThrobber();

        const getError = (error) => {
            if (typeof error === 'object' && error !== null && error.message) {
                error = error.message;
            }
            return error;
        }

        const onPayError = (error) => {
            error = getError(error);
            this.showPaymentError(error, placementId);
            BrippoPayments.hideVeilThrobber();
            if (errorHandler) {
                errorHandler(error);
            }
        }

        /*
         * SUBMIT ELEMENTS
         */
        try {
            const {error: submitError} = await this.elementsByPlacement[placementId].submit();
            if (submitError) {
                onPayError(submitError);
                return;
            }
        } catch (ex) {
            onPayError(ex.message);
            BrippoPayments.log('Payment Element [' + placementId + '] submit elements', ex.message);
            return;
        }

        /*
         * CREATE PAYMENT METHOD
         */
        console.log('Brippo Payment Element: Creating payment method...');
        let paymentMethod;
        if (typeof brippoRecoverOrderBillingAddress !== 'undefined') {
            billingDetails = brippoRecoverOrderBillingAddress;
        }
        if (this.customerEmail && (!billingDetails || !billingDetails.email || billingDetails.email === '')) {
            if (!billingDetails) {
                billingDetails = {};
            }
            billingDetails.email = this.customerEmail;
        }

        try {
            const {error: paymentMethodError, paymentMethod: paymentMethodResponse} = await BrippoPayments.stripe.createPaymentMethod({
                elements: this.elementsByPlacement[placementId],
                ...((billingDetails) && {
                    params: {
                        billing_details: billingDetails
                    }
                })
            });
            if (paymentMethodError) {
                onPayError(paymentMethodError);
                return;
            }
            paymentMethod = paymentMethodResponse;
        } catch (ex) {
            onPayError(ex.message);
            BrippoPayments.log('Payment Element [' + placementId + '] create payment methods', ex.message);
            return;
        }

        let clientSecret, orderId, paymentIntentId, orderIncrementId;
        if (placementId === this.PLACEMENT_ID.RECOVER_CHECKOUT) {
            /*
             * CREATE PAYMENT INTENT
             */
            console.log('Brippo Payment Element: Creating Payment Intent...');
            const {error: recoverOrderError, clientSecret:recoverOrderClientSecret, orderId:recoverOrderOrderId,
                paymentIntentId:recoverOrderPaymentIntentId, orderIncrementId:recoverOrderOrderIncrementId} = await this.recoverOrder(paymentMethod);
            if (recoverOrderError) {
                onPayError(recoverOrderError);
                BrippoPayments.log('Payment Element [' + placementId + '] recover order', recoverOrderError);
                return;
            }
            clientSecret = recoverOrderClientSecret;
            orderId = recoverOrderOrderId;
            paymentIntentId = recoverOrderPaymentIntentId;
            orderIncrementId = recoverOrderOrderIncrementId;
        } else {
            /*
             * PLACE ORDER & PI
             */
            console.log('Brippo Payment Element: Placing order & Payment Intent...');
            const {error: placeOrderError, clientSecret:clientSecretNewOrder,
                orderId: orderIdNewOrder, paymentIntentId: paymentIntentIdNewOrder,
                orderIncrementId: orderIncrementIdNewOrder} = await this.placeOrderAndPaymentIntent(
                    placementId,
                    paymentMethod,
                    checkoutBillingAddress,
                    checkoutShippingAddress,
                    checkoutCustomerEmail
            );
            if (placeOrderError) {
                onPayError(placeOrderError);
                BrippoPayments.log('Payment Element [' + this.placementId + '] place order & pi', placeOrderError);
                return;
            }
            clientSecret = clientSecretNewOrder;
            orderId = orderIdNewOrder;
            paymentIntentId = paymentIntentIdNewOrder;
            orderIncrementId = orderIncrementIdNewOrder;
        }

        /*
         * CONFIRM PAYMENT INTENT
         */
        console.log('Brippo Payment Element: Confirming payment...');
        try {
            const responseUrl = BrippoPayments.config.paymentElement.controllers.response
                + '?orderId=' + orderId
                + '&isOrderRecover=' + (placementId === this.PLACEMENT_ID.RECOVER_CHECKOUT ? 'true' : 'false');
            const {error: confirmError} = await BrippoPayments.stripe.confirmPayment({
                clientSecret,
                confirmParams: {
                    payment_method: paymentMethod.id,
                    return_url: responseUrl,
                    ...((shippingDetails) ? {
                        shipping: shippingDetails
                    }: {}),
                }
            });

            if (confirmError) {
                BrippoPayments.logOrderEvent(orderId, 'Stripe confirm error: ' + (confirmError.message ?? JSON.stringify(confirmError)));
                BrippoPayments.timesPaymentConfirmationFailed++;
                const paymentIntent  = await BrippoPayments.stripe.retrievePaymentIntent(clientSecret);
                if (paymentIntent.status === "succeeded"
                    || BrippoPayments.isDeclineCodeAllowedForRecovery(confirmError)) { //Allow to recover later
                    BrippoPayments.logOrderEvent(orderId, 'Payment Element [' + placementId + '] confirm failed but payment status is success', getError(confirmError));
                    window.location.href = responseUrl + "&payment_intent=" + paymentIntentId;
                } else {
                    onPayError(confirmError);
                    if (placementId !== this.PLACEMENT_ID.RECOVER_CHECKOUT) {
                        await this.cancelOrder(
                            placementId,
                            orderId,
                            orderIncrementId,
                            'Payment declined: ' + confirmError.message ?? JSON.stringify(confirmError)
                        );
                    }
                    BrippoPayments.log('Payment Element [' + placementId + '] confirm', getError(confirmError));
                }
            }
        } catch (ex) {
            BrippoPayments.logOrderEvent(orderId, 'Stripe confirm exception: ' + ex.message);
            onPayError(ex.message);
            if (placementId !== this.PLACEMENT_ID.RECOVER_CHECKOUT) {
                await this.cancelOrder(
                    placementId,
                    orderId,
                    orderIncrementId,
                    ex.message
                );
            }
            BrippoPayments.log('Payment Element [' + placementId + '] confirm exception', ex.message);
        }
    },
    async recoverOrder(paymentMethod, placementId = this.PLACEMENT_ID.RECOVER_CHECKOUT) {
        this.resetPaymentErrors(placementId);
        let clientSecret, orderId, orderIncrementId, paymentIntentId, error;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.paymentElement.controllers.recoverOrder);
        await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                paymentMethod: paymentMethod,
                recoverOrderId: brippoRecoverOrderEntityId,
                linkSource: brippoRecoverLinkSource,
                isManual: brippoRecoverIsManual,
                isSoftFailRecovery: brippoRecoverIsSoftFailRecovery,
                notificationNumber: brippoRecoverNotification
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText + ' (Status code ' + response.status + ')');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.valid === 1) {
                    orderId = data.order_id;
                    orderIncrementId = data.order_increment_id;
                    paymentIntentId = data.payment_intent_id;
                    clientSecret = data.client_secret;
                } else {
                    error = data.message;
                }
            })
            .catch(err => {
                error = BrippoPayments.prettifyErrorMessage(err.message);
                console.error('There was a problem while fetching payment element recoverOrder:', error);
            });

        return { error, clientSecret, orderId, orderIncrementId, paymentIntentId }
    },
    async cancelOrder(placementId, orderId, orderIncrementId, errorMessage) {
        let error;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.paymentElement.controllers.cancel);
        await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                orderId : orderId,
                orderIncrementId : orderIncrementId,
                error: errorMessage
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText + ' (Status code ' + response.status + ')');
                }
                return response.json();
            })
            .then(data => {
                if (data.valid === 0) {
                    BrippoPayments.log('[' + placementId + '] cancel', data.message);
                    error = data.message
                }
            })
            .catch(err => {
                error = BrippoPayments.prettifyErrorMessage(err.message);
                console.error('There was a problem while fetching payment element cancelOrder:', error);
            });
    },
    async placeOrderAndPaymentIntent(placementId, paymentMethod, billingAddress, shippingAddress, customerEmail) {
        let error, clientSecret, orderId, paymentIntentId, orderIncrementId;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.paymentElement.controllers.placeOrder);
        await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                placementId: placementId,
                paymentMethod: paymentMethod,
                billingAddress: billingAddress,
                shippingAddress: shippingAddress,
                email: customerEmail,
                isRecovery: false
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText + ' (Status code ' + response.status + ')');
                }
                return response.json();
            })
            .then(data => {
                if (data.valid === 0) {
                    BrippoPayments.log('[' + placementId + '] cancel', data.message);
                    error = data.message
                } else {
                    clientSecret = data.client_secret;
                    orderId = data.order_id;
                    paymentIntentId = data.payment_intent_id;
                    orderIncrementId = data.order_increment_id;
                }
            })
            .catch(err => {
                error = BrippoPayments.prettifyErrorMessage(err.message);
                console.error('There was a problem while fetching payment element placeOrder:', error);
            });

        return {error, clientSecret, orderId, paymentIntentId, orderIncrementId}
    },
    onCartTotalUpdated() {
        window.brippoPaymentElement.placementsSubscribedToCartUpdates.forEach((placementId) => {
            window.brippoPaymentElement.initializePaymentElement(placementId, null).then();
        });
    },
}

document.addEventListener('onBrippoPaymentsInitializationComplete', () => {
    window.brippoPaymentElement.onBrippoPaymentsInitialized();
});
window.brippoPaymentElement.initialize().then();

if (typeof require === 'function') {
    require([
        'Magento_Customer/js/customer-data'
    ], function (customerData) {
        if (customerData) {
            let cart = customerData.get('cart');
            cart.subscribe(() => {
                setTimeout(window.brippoPaymentElement.onCartTotalUpdated, 500);
            });
        }
    });
}

