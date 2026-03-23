
window.brippoExpressCheckoutElement = {
    PLACEMENT_ID: {
        PRODUCT_PAGE: 'product_page',
        MINICART: 'minicart',
        CART: 'cart',
        CHECKOUT_TOP: 'checkout',
        RECOVER_CHECKOUT: 'recover_checkout',
        CHECKOUT_LIST: 'checkout_list'
    },
    EVENTS: {
        beforePlaceOrder: null
    },
    elementsByPlacement: {},
    expressCheckoutElementByPlacement: {},
    lastTotalRequestedByPlacement: {},
    lastCartUpdateByPlacement: {},
    canMakePayments: null,
    placementsSubscribedToCartUpdates: [],
    invalidateCart: null,
    wasInitialized: false,
    wasInsertedIntoMinicart: false,
    paymentRequestByPlacement: {},
    keepRegeneratingIfGoneCounter: 0,
    checkout: {
        shippingAddress: null,
        billingAddress: null,
        shippingMethod: null,
        shippingDetails: null,
        email: null,
    },
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

        if (BrippoPayments.config.expressCheckoutElement.enabled !== "1"
            && !BrippoPayments.isRecoverCheckout()) {
            return;
        } else if (BrippoPayments.isRecoverCheckout()
            && typeof brippoRecoverIsStockAvailable !== 'undefined'
            && !brippoRecoverIsStockAvailable) {
            return;
        }

        /**
         * CART
         */
        if (window.location.href.indexOf('/checkout/cart') !== -1
            && BrippoPayments.config.expressCheckoutElement[this.PLACEMENT_ID.CART].enabled === "1") {
            BrippoPayments.waitForElementToExists(
                this.getPlacementSelector(this.PLACEMENT_ID.CART),
                () => {
                    this.insertPlaceholder(this.PLACEMENT_ID.CART, this.generatePlaceholder(this.PLACEMENT_ID.CART));
                    this.initializeExpressCheckout(this.PLACEMENT_ID.CART).then();
                }
            );
        }

        /**
         * PRODUCT
         */
        if (document?.body?.classList
            && document.body.classList.contains('catalog-product-view')
            && BrippoPayments.config.expressCheckoutElement[this.PLACEMENT_ID.PRODUCT_PAGE].enabled === "1") {
            BrippoPayments.waitForElementToExists(
                this.getPlacementSelector(this.PLACEMENT_ID.PRODUCT_PAGE),
                () => {
                    this.insertPlaceholder(
                        this.PLACEMENT_ID.PRODUCT_PAGE,
                        this.generatePlaceholder(this.PLACEMENT_ID.PRODUCT_PAGE)
                    );
                    this.initializeExpressCheckout(this.PLACEMENT_ID.PRODUCT_PAGE).then(() => {
                        this.keepRegeneratingIfGone(this.PLACEMENT_ID.PRODUCT_PAGE);
                    });
                }
            );
        }

        /**
         * MINICART
         */
        if (BrippoPayments.config.expressCheckoutElement[this.PLACEMENT_ID.MINICART].enabled === "1") {
            this.setupMinicart();
        }

        /**
         * CHECKOUT
         */
        if ((document.body.classList.contains('checkout-index-index')
            || document.body.classList.contains('hyva_checkout-index-index'))
            && BrippoPayments.config.expressCheckoutElement[this.PLACEMENT_ID.CHECKOUT_TOP].enabled === "1") {
            BrippoPayments.waitForElementToExists(
                this.getPlacementSelector(this.PLACEMENT_ID.CHECKOUT_TOP),
                () => {
                    this.insertPlaceholder(
                        this.PLACEMENT_ID.CHECKOUT_TOP,
                        this.generatePlaceholder(this.PLACEMENT_ID.CHECKOUT_TOP)
                    );
                    this.initializeExpressCheckout(this.PLACEMENT_ID.CHECKOUT_TOP).then();
                }
            );
        }

        /**
         * RECOVER
         */
        if (BrippoPayments.isRecoverCheckout()) {
            this.initializeExpressCheckout(this.PLACEMENT_ID.RECOVER_CHECKOUT).then();
        }
    },
    async initializeExpressCheckout(placementId) {
        if (!this.wasInitialized) {
            return;
        }

        const container = document.getElementById(this.getElementUniqueId(placementId, 'container'));

        if (!(this.placementsSubscribedToCartUpdates.includes(placementId))) {
            this.placementsSubscribedToCartUpdates.push(placementId);
        }

        const {error: paymentRequestOptionsError, paymentRequestOptions, checkoutOptions, elementsOptions, customerInfo} = await this.getPaymentRequestOptions(placementId);

        if (paymentRequestOptionsError) {
            this.showPaymentError(placementId, paymentRequestOptionsError);
            return;
        }

        const blockedGroups = BrippoPayments.config.expressCheckoutElement['blockedCustomerGroups'];
        if (blockedGroups && blockedGroups.includes(customerInfo.groupId)) {
            console.log('Brippo won’t create payment request for Customer Group [' + customerInfo.groupId + '] ');
            if (container) {
                container.style.display = 'none'; // Hide the container
            }
            return;
        }

        let amount = parseFloat(paymentRequestOptions.amount);

        //Disable for 0 amount
        if (amount <= 0) {
            console.log('[' + placementId + '] Brippo won’t create payment request for 0');
            if (container) {
                container.style.display = 'none'; // Hide the container
            }
            return;
        }

        if (placementId !== this.PLACEMENT_ID.RECOVER_CHECKOUT) {
            //Disable if thresholds are not met
            let minThreshold = parseInt(BrippoPayments.config.expressCheckoutElement[placementId].minAmount);
            let maxThreshold = parseInt(BrippoPayments.config.expressCheckoutElement[placementId].maxAmount);
            if ((minThreshold && amount / 100 < minThreshold) ||
                (maxThreshold && amount / 100 > maxThreshold)) {
                console.log(
                    '[' + placementId + '] Brippo won’t create payment request for amounts outside the margin' +
                    '(' + BrippoPayments.config.expressCheckoutElement[placementId].minAmount + ' - ' +
                    BrippoPayments.config.expressCheckoutElement[placementId].maxAmount + ')'
                );
                if (container) {
                    container.style.display = 'none'; // Hide the container
                }
                return;
            }
        }

        if (container) {
            container.style.display = 'block'; // Show the container
        }

        // Avoid re-requesting for the same amount
        if (placementId in this.lastTotalRequestedByPlacement &&
            this.lastTotalRequestedByPlacement[placementId] === paymentRequestOptions.amount) {
            return;
        }

        this.lastTotalRequestedByPlacement[placementId] = paymentRequestOptions.amount;
        this.paymentRequestByPlacement[placementId] = {
            paymentRequestOptions: paymentRequestOptions,
            checkoutOptions: checkoutOptions,
            elementsOptions: elementsOptions
        }

        if (placementId !== this.PLACEMENT_ID.MINICART || this.wasInsertedIntoMinicart) {
            this.checkMountElementHealth(placementId, () => {
                this.createExpressCheckoutElement(placementId, paymentRequestOptions, checkoutOptions, elementsOptions);
                BrippoPayments.reportAnalytic('magento2_ece_' + placementId);
            });
        }
    },
    generatePlaceholder(placementId) {
        const orSeparatorMode = BrippoPayments.config.expressCheckoutElement[placementId].orSeparator;
        const separatorHtml = '<div class="brippoExpressOrSeparator"><span class="separatorLine"></span><span class="separatorOr">OR</span><span class="separatorLine"></span></div>';

        let placeholderHtml = '<div id="' + this.getElementUniqueId(placementId, 'container') + '">';

        if ('title' in BrippoPayments.config.expressCheckoutElement[placementId]
            && BrippoPayments.config.expressCheckoutElement[placementId].title !== '') {
            placeholderHtml += '<div class="step-title title abs-checkout-title expressCheckoutTitle">'
                + BrippoPayments.config.expressCheckoutElement[placementId].title + '</div>';
        }

        if (orSeparatorMode === 'before') {
            placeholderHtml += separatorHtml;
        }
        placeholderHtml += '<div id="' + this.getElementUniqueId(placementId, 'error') + '" class="expressCheckoutError"></div>'
            + '<div id="' + this.getElementUniqueId(placementId, 'mount') + '"></div>';

        if (orSeparatorMode === 'after') {
            placeholderHtml += separatorHtml;
        }
        placeholderHtml += '</div>';

        return placeholderHtml;
    },
    insertPlaceholder(placementId, html) {
        const placementElement = document.querySelector(this.getPlacementSelector(placementId));
        let insertMode = 'afterBegin';
        if (placementId in BrippoPayments.config.expressCheckoutElement
            && BrippoPayments.config.expressCheckoutElement[placementId].placementMode) {
            insertMode = BrippoPayments.config.expressCheckoutElement[placementId].placementMode;
        }
        placementElement.insertAdjacentHTML(insertMode, html);
    },
    getElementUniqueId(placementId, type) {
        const uniqueId = 'brippoExpressCheckout' + placementId;
        return uniqueId + '_' + type;
    },
    showPaymentError: function (placementId, message) {
        let span = document.getElementById(this.getElementUniqueId(placementId, 'error'));
        if (span) {
            span.innerHTML = '<span>' + message + '</span>';
            span.style.display = "block";
        }
    },
    resetPaymentErrors: function (placementId) {
        let span = document.getElementById(this.getElementUniqueId(placementId, 'error'));
        if (span) {
            span.style.display = "none";
        }
    },
    checkMountElementHealth(placementId, callback) {
        /*
         * In case mount element was removed. Regenerate if needed.
         */
        if (document.getElementById(this.getElementUniqueId(placementId, 'mount'))) {
            callback(true);
        } else {
            // Regenerate
            const selector = this.getPlacementSelector(placementId);
            BrippoPayments.waitForElementToExists(selector, () => {
                const element = document.querySelector(selector);
                if (element) {
                    element.innerHTML += this.generatePlaceholder(placementId);
                    callback(true);
                }
            });
        }
    },
    getPlacementSelector(placementId) {
        let placementSelector;
        switch (placementId) {
            case this.PLACEMENT_ID.PRODUCT_PAGE:
                placementSelector = '.box-tocart .actions, .items-end';
                break;
            case this.PLACEMENT_ID.CART:
                placementSelector = 'ul.checkout-methods-items';
                break;
            case this.PLACEMENT_ID.MINICART:
                placementSelector = '#minicart-content-wrapper .actions, #cart-drawer [role="dialog"] .relative .flex';
                break;
            case this.PLACEMENT_ID.CHECKOUT_TOP:
                if (BrippoPayments.isMobileScreen()) {
                    placementSelector = '#checkoutSteps';
                } else {
                    placementSelector = '.opc-block-summary, .checkout-onepage .area-right, .grid';
                }
                break;
        }

        if (placementId in BrippoPayments.config.expressCheckoutElement
            && BrippoPayments.config.expressCheckoutElement[placementId].placementSelector
            && BrippoPayments.config.expressCheckoutElement[placementId].placementSelector !== '') {
            placementSelector = BrippoPayments.config.expressCheckoutElement[placementId].placementSelector
        }
        return placementSelector;
    },
    async getPaymentRequestOptions(placementId) {
        this.resetPaymentErrors(placementId);
        let error, paymentRequestOptions, checkoutOptions, elementsOptions, appliedCoupon, customerInfo;

        const productInput = document.querySelector('input[name="product"]');
        const currentProductId = productInput ? productInput.value : null;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.expressCheckoutElement.controllers.paymentRequest);
        const payload = {
            placementId,
            currentProductId,
            ...((placementId === this.PLACEMENT_ID.RECOVER_CHECKOUT) && {
                recoverOrderId: brippoRecoverOrderEntityId
            })
        };

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error(`${response.statusText} (Status code ${response.status})`);
            }

            const data = await response.json();

            if (data && data.valid === 1) {
                console.log(
                    `[${placementId}] Brippo Express Checkout payment request completed for ${data.options.amount}.`
                );
                paymentRequestOptions = data.options;
                checkoutOptions        = data.checkoutOptions;
                elementsOptions        = data.elementsOptions;
                customerInfo           = data.customer;
                appliedCoupon          = data.coupon;
            } else {
                throw new Error(data.message ||
                    `[${placementId}] An error occurred while processing the payment request.`);
            }
        } catch (err) {
            error = BrippoPayments.prettifyErrorMessage(err.message);
            console.error(
                `[${placementId}] Failed fetching express checkout request:`, error
            );
            console.error(`[${placementId}] No more retries left.`);
        }

        return {error, paymentRequestOptions, checkoutOptions, elementsOptions, customerInfo, appliedCoupon};
    },
    createExpressCheckoutElement(placementId, options, checkoutOptions, elementsOptions) {
        const elements = BrippoPayments.stripe.elements(this.failProofOptions(options, checkoutOptions));
        this.elementsByPlacement[placementId] = elements;
        const expressCheckoutElement = elements.create('expressCheckout', elementsOptions);
        this.mountExpressCheckout(placementId, expressCheckoutElement, checkoutOptions);
    },
    mountExpressCheckout(placementId, expressCheckoutElement, checkoutOptions) {

        const elementToMountOn = document.getElementById(this.getElementUniqueId(placementId, 'mount'));
        elementToMountOn.style.visibility = 'hidden';
        expressCheckoutElement.mount('#' + this.getElementUniqueId(placementId, 'mount'));

        expressCheckoutElement.on('ready', ({availablePaymentMethods}) => {
            this.onReadyHandler(placementId, availablePaymentMethods, elementToMountOn);
        });

        expressCheckoutElement.on('click', (event) => {

            let productPageRequest;
            if (placementId === this.PLACEMENT_ID.PRODUCT_PAGE) {
                const {error: validationError, request} = this.validateProductPageSelection();
                if (validationError) {
                    this.showPaymentError(placementId, validationError);
                    return;
                }
                productPageRequest = request;
            }

            const {error} = this.onWalletButtonClickHandler(placementId, productPageRequest);
            if (error) {
                this.showPaymentError(placementId, error);
            } else {
                event.resolve(this.failproofCheckoutOptions(placementId, checkoutOptions, this.elementsByPlacement[placementId]?._commonOptions?.amount));
            }
        });

        expressCheckoutElement.on('shippingaddresschange', async(event) => {
            const {error, updatedOptions, updatedCheckoutOptions} = await this.onShippingAddressChangeHandler(placementId, event.address);
            if (error) {
                event.reject();
            } else {
                this.updateElementsIfRequired(placementId, updatedOptions);
                event.resolve(updatedCheckoutOptions);
            }
        });

        expressCheckoutElement.on('shippingratechange', async(event) => {
            const {error, updatedOptions, updatedCheckoutOptions} = await this.onShippingOptionChangeHandler(placementId, event.shippingRate);
            if (error) {
                event.reject();
            } else {
                this.updateElementsIfRequired(placementId, updatedOptions);
                event.resolve(updatedCheckoutOptions);
            }
        });

        expressCheckoutElement.on('confirm', async(event) => {
            await this.onConfirmHandler(placementId, event);
        });

        expressCheckoutElement.on('cancel', () => {
            this.onCancelHandler(placementId);
        });
    },
    onReadyHandler(placementId, availablePaymentMethods, mountOnElement) {
        if (!availablePaymentMethods) {
            this.canMakePayments = false;
            console.log('No available payment methods available');
        } else {
            this.canMakePayments = true;
            mountOnElement.style.visibility = 'initial';
            const orSeparator = document.querySelector('#' + this.getElementUniqueId(placementId, 'container') + ' .brippoExpressOrSeparator');
            if (orSeparator) {
                orSeparator.style.display = 'flex';
            }
            const title = document.querySelector('#' + this.getElementUniqueId(placementId, 'container') + ' .expressCheckoutTitle');
            if (title) {
                title.style.display = 'block';
            }
            document.dispatchEvent(new CustomEvent('brippoOnIntegrationReady', {
                detail: {
                    code: 'brippo_payments_ece',
                    placementId: placementId,
                    availablePaymentMethods: availablePaymentMethods
                }
            }));
        }
    },
    onCancelHandler(placementId) {
        console.log('[' + placementId + '] onCancelHandler');
        BrippoPayments.hideVeilThrobber();
    },
    async onConfirmHandler(placementId, event) {
        console.log('[' + placementId + '] onConfirmHandler');
        BrippoPayments.showVeilThrobber(placementId);

        const elements = this.elementsByPlacement[placementId];
        const shippingAddress = event.shippingAddress;
        const billingDetails = event.billingDetails;

        const errorHandler = (step, errorMessage) => {
            BrippoPayments.hideVeilThrobber();
            if (typeof errorMessage === 'object') {
                if (errorMessage.message) {
                    errorMessage = errorMessage.message;
                } else {
                    errorMessage = JSON.stringify(errorMessage);
                }
            }
            BrippoPayments.log('[' + placementId + '] ' + step, errorMessage);
            this.showPaymentError(placementId, errorMessage);
            return errorMessage;
        }

        /*
         * SUBMIT
         */
        console.log('[' + placementId + '] Brippo: Submitting elements');
        const {error: submitError} = await elements.submit();
        if (submitError) {
            errorHandler('submitting elements', submitError);
            return;
        }

        /*
         * CREATE PAYMENT METHOD
         */
        console.log('[' + placementId + '] Brippo: Creating payment method...');
        const {error: paymentMethodError, paymentMethod} = await BrippoPayments.stripe.createPaymentMethod({
            elements,
            params: {
                billing_details: billingDetails
            }
        });
    if (paymentMethodError) {
        errorHandler('creating payment method', paymentMethodError);
        return;
    }

    let clientSecret, orderId, paymentIntentId, orderIncrementId;
    if (placementId === this.PLACEMENT_ID.RECOVER_CHECKOUT) {
        /*
         * CREATE PAYMENT INTENT for order recover
         */
        console.log('[' + placementId + '] Brippo: Creating Payment Intent to recover order...');
        const {error: recoverOrderError, clientSecret:recoverOrderClientSecret,
            orderId:recoverOrderOrderId, paymentIntentId:recoverOrderPaymentIntentId,
            orderIncrementId:recoverOrderOrderIncrementId} = await this.recoverOrder(placementId, paymentMethod);
        if (recoverOrderError) {
            errorHandler('creating pi for recover order', recoverOrderError);
            return;
        }
        clientSecret = recoverOrderClientSecret;
        orderId = recoverOrderOrderId;
        paymentIntentId = recoverOrderPaymentIntentId;
        orderIncrementId = recoverOrderOrderIncrementId;
    } else {
        /*
         * PLACE ORDER AND CREATE PAYMENT
         */
        console.log('[' + placementId + '] Brippo: Placing order & Payment Intent...');
        const {
            error: placeOrderError,
            clientSecret:placeOrderClientSecret,
            orderId: placeOrderOrderId,
            orderIncrementId: placeOrderOrderIncrementId,
            paymentIntentId: placeOrderPaymentIntentId
        } = await this.placeOrderAndCreatePaymentIntent(
            placementId,
            paymentMethod,
            billingDetails,
            shippingAddress
        );
        if (placeOrderError) {
            errorHandler('placing order & pi', placeOrderError);
            return;
        }
        clientSecret = placeOrderClientSecret;
        orderId = placeOrderOrderId;
        paymentIntentId = placeOrderPaymentIntentId;
        orderIncrementId = placeOrderOrderIncrementId;
    }

        /*
         * CONFIRM PAYMENT
         */
        console.log('[' + placementId + '] Brippo: Confirming payment...');
        await BrippoPayments.stripe.confirmPayment({
            clientSecret,
            confirmParams: {
                payment_method: paymentMethod.id,
                ...((this.checkout.shippingDetails) ? {
                    shipping: this.checkout.shippingDetails
                }: {}),
            },
            redirect: 'if_required'
        }).then(async(result) => {
            if (result.error) {
                const failError = errorHandler('confirming payment', result.error);
                if (placementId !== this.PLACEMENT_ID.RECOVER_CHECKOUT) {
                    await this.cancelOrder(
                        placementId,
                        orderId,
                        orderIncrementId,
                        failError
                    );
                }
            } else {
                const paymentIntent = result.paymentIntent;
                /*
                 * COMPLETE ORDER
                 */
                console.log('[' + placementId + '] Brippo: Completing order #' + orderIncrementId + '...');
                const {error: completeError} = await this.completeOrder(
                    placementId,
                    orderId,
                    orderIncrementId,
                    paymentIntent.id,
                    paymentIntent.status
                );
                if (completeError) {
                    BrippoPayments.hideVeilThrobber();
                    BrippoPayments.log('[' + placementId + '] complete', completeError);
                }
            }
        });
    },
    async placeOrderAndCreatePaymentIntent(placementId, paymentMethod, billingDetails, shippingAddress) {
        this.resetPaymentErrors(placementId);
        document.dispatchEvent(this.EVENTS.beforePlaceOrder);
        let clientSecret, orderId, orderIncrementId, paymentIntentId, error;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.expressCheckoutElement.controllers.placeOrder);

        await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                paymentMethod: paymentMethod,
                billingDetails: billingDetails,
                shippingAddress: shippingAddress,
                placementId: placementId,
                pickupInputValues: this.pickupInputValues(),
                checkoutBillingAddress: this.checkout.billingAddress,
                checkoutShippingAddress: this.checkout.shippingAddress,
                checkoutShippingMethod: this.checkout.shippingMethod,
                checkoutEmail: this.checkout.email,
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
                console.error('There was a problem while fetching express checkout placeOrder:', error);
            });

        return { error, clientSecret, orderId, orderIncrementId, paymentIntentId }
    },
    async recoverOrder(placementId, paymentMethod) {
        this.resetPaymentErrors(placementId);
        let clientSecret, orderId, orderIncrementId, paymentIntentId, error;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.expressCheckoutElement.controllers.recoverOrder);

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
                console.error('There was a problem while fetching express checkout recoverOrder:', error);
            });

        return { error, clientSecret, orderId, orderIncrementId, paymentIntentId }
    },
    async completeOrder(placementId, orderId, orderIncrementId, paymentIntentId, paymentIntentStatus) {
        let error;

        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.expressCheckoutElement.controllers.complete);
        await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                orderId,
                orderIncrementId,
                paymentIntentId,
                paymentIntentStatus,
                ...((placementId === this.PLACEMENT_ID.RECOVER_CHECKOUT) && {
                    isOrderRecover: true
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
                if (window.brippoExpressCheckoutElement.invalidateCart) {
                    window.brippoExpressCheckoutElement.invalidateCart();
                }
                location.href = data.url;
            })
            .catch(err => {
                error = BrippoPayments.prettifyErrorMessage(err.message);
                console.error('There was a problem while fetching express checkout complete:', error);
            });

        return { error };
    },
    onShippingOptionChangeHandler: async function (placementId, shippingRate) {
        console.log('[' + placementId + '] Brippo: Shipping option changed...');
        this.resetPaymentErrors(placementId);

        let error, updatedOptions, updatedCheckoutOptions;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.expressCheckoutElement.controllers.addShippingMethod);

        await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                shippingOption: shippingRate,
                placementId: placementId
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
                    updatedOptions = data.options;
                    updatedCheckoutOptions = data.checkoutOptions;
                } else {
                    error = data.message;
                }
            })
            .catch(err => {
                error = BrippoPayments.prettifyErrorMessage(err.message);
                console.error('There was a problem while fetching express checkout addShippingMethod:', error);
            });

        if (error) {
            this.showPaymentError(placementId, error);
        }

        return { error, updatedOptions, updatedCheckoutOptions }
    },
    onShippingAddressChangeHandler: async function (placementId, address) {
        console.log('[' + placementId + '] Brippo: Shipping address changed...');
        this.resetPaymentErrors(placementId);
        this.shippingAddress = address;

        let error, updatedOptions, updatedCheckoutOptions;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.expressCheckoutElement.controllers.addShippingAddress);

        // Get selected delivery option from cart page if available
        const selectedDeliveryOption = this.getSelectedDeliveryOptionFromCart(placementId);

        await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                shippingAddress: address,
                placementId: placementId,
                selectedDeliveryOption: selectedDeliveryOption
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
                    updatedOptions = data.options;
                    updatedCheckoutOptions = data.checkoutOptions;
                } else {
                    error = data.message;
                }
            })
            .catch(err => {
                error = BrippoPayments.prettifyErrorMessage(err.message);
                console.error('There was a problem while fetching express checkout addShippingAddress:', error);
            });

        if (error) {
            this.showPaymentError(placementId, error);
        }

        return { error, updatedOptions, updatedCheckoutOptions }
    },
    updateElementsIfRequired(placementId, options) {
        if (this.lastTotalRequestedByPlacement[placementId] !== options.amount) {
            this.lastTotalRequestedByPlacement[placementId] = options.amount;
            this.elementsByPlacement[placementId].update({amount: options.amount});
        }
    },
    onCartTotalUpdated() {
        window.brippoExpressCheckoutElement.placementsSubscribedToCartUpdates.forEach((placementId) => {
            window.brippoExpressCheckoutElement.initializeExpressCheckout(placementId).then();
        });
    },
    validateProductPageSelection() {
        const form = document.getElementById('product_addtocart_form');
        let request = [];
        let error;

        let isValid = true;
        const requiredInputs = form.querySelectorAll(
            'input[aria-required="true"], input[data-validate],' +
            ' select[aria-required="true"], select[data-validate]'
        );
        requiredInputs.forEach(input => {
            if (!input.value || input.value.trim() === '') {
                error = 'Please select all required options for this product.';
            }

            const validateQuantities = () => {
                try {
                    const validateRules = JSON.parse(input.getAttribute('data-validate'));
                    if (!validateRules || !('validate-item-quantity' in validateRules)) {
                        return;
                    }

                    const minAllowed = validateRules['validate-item-quantity'].minAllowed;
                    const maxAllowed = validateRules['validate-item-quantity'].maxAllowed;
                    const currentValue = parseFloat(input.value);
                    if (!isNaN(minAllowed) && currentValue < minAllowed) {
                        error = `Minimum quantity allowed is ${minAllowed}.`;
                    }

                    // Validate if the value exceeds the maximum allowed quantity
                    if (!isNaN(maxAllowed) && currentValue > maxAllowed) {
                        error = `Maximum quantity allowed is ${maxAllowed}.`;
                    }
                } catch (err) {
                    //console.log(err);
                }
            }

            if (input.type === 'number' && input.getAttribute('data-validate')) {
                validateQuantities();
            }
        });

        /*
         * VALIDATE GROUPED QTY
         */
        let totalQty = 0;
        const qtyInputs = form.querySelectorAll('input[type="number"][data-validate*="validate-grouped-qty"]');
    if (qtyInputs.length > 0) {
        qtyInputs.forEach(input => {
            totalQty += parseFloat(input.value);
            });
        if (totalQty === 0) {
            error = 'Please specify the quantity of product(s).';
        }
    }

        const formData = new FormData(form);
        request = new URLSearchParams(formData).toString();

        return { error, request };
    },
    async onWalletButtonClickHandler(placementId, productPageRequest) {
        if (placementId === this.PLACEMENT_ID.PRODUCT_PAGE) {
            document.getElementById(this.getElementUniqueId(placementId, 'mount')).classList.add('disabled');
            const { error: addToCartError, addToCartResponse } = await this.addToCart(placementId, productPageRequest);
            document.getElementById(this.getElementUniqueId(placementId, 'mount')).classList.remove('disabled');

            if (addToCartError) {
                return {error: addToCartError}
            }

            try {
                this.updateElementsIfRequired(placementId, addToCartResponse.options);
                console.log('[' + placementId + '] onConfirmHandler');
                console.log('Brippo [' + placementId + ']: Express Checkout payment request updated for ' + addToCartResponse.options.total.amount + '.');
            } catch (err) {
                console.log(err);
            }
        }

        return { error: null }
    },
    async addToCart(placementId, request) {
        this.resetPaymentErrors(placementId);
        let error;
        let addToCartResponse;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.expressCheckoutElement.controllers.addToCart);

        BrippoPayments.showVeilThrobber();
        await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                request: request,
                shippingAddress: this.shippingAddress
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText + ' (Status code ' + response.status + ')');
                }
                return response.json();
            })
            .then(data => {
                if (data.valid === 1) {
                    addToCartResponse = data
                } else {
                    error = data.message;
                }
            })
            .catch(err => {
                error = BrippoPayments.prettifyErrorMessage(err.message);
                console.error('There was a problem while fetching express checkout addToCart:', error);
            });

    if (error) {
        BrippoPayments.hideVeilThrobber();
        this.showPaymentError(placementId, error);
    }

        return { error, addToCartResponse }
    },
    bindConfigurableProductOptions: function (config, source) {
        const self = this;
        const options = jQuery("#product-options-wrapper .configurable select.super-attribute-select");
        options.each(function (index) {
            const onConfigurableProductChanged = self.onConfigurableProductChanged.bind(self, this, config, source);
            jQuery(this).change(onConfigurableProductChanged);
        });
    },
    onConfigurableProductChanged: function (element, config, source) {
        const self = this;
        if (element.value) {
            config.currentProductId = element.value;
            this.initPaymentRequest(config, source);
        }
    },
    setupMinicart() {
        const setupMinicartAfterDomLoaded = () => {
            const miniCartButton = document.querySelector('.showcart, #menu-cart-icon');
            if (miniCartButton) {
                const eventType = BrippoPayments.config.expressCheckoutElement[this.PLACEMENT_ID.MINICART].eventType;
                if (eventType === 'click' || eventType === "both") {
                        miniCartButton.addEventListener('click', () => {
                            this.onMinicartButtonClick();
                        });
                }
                if (eventType === 'hover' || eventType === "both") {
                    miniCartButton.addEventListener('mouseover', () => {
                        this.onMinicartButtonClick();
                    });
                }

                this.initializeExpressCheckout(this.PLACEMENT_ID.MINICART).then();
            } else {
                console.error('Brippo: Mini cart button not found.');
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setupMinicartAfterDomLoaded();
            });
        } else {
            setupMinicartAfterDomLoaded();
        }
    },
    onMinicartButtonClick() {
        if (!this.wasInsertedIntoMinicart) {
            this.wasInsertedIntoMinicart = true;
            BrippoPayments.waitForElementToExists(
                this.getPlacementSelector(this.PLACEMENT_ID.MINICART),
                () => {
                this.insertPlaceholder(
                        this.PLACEMENT_ID.MINICART,
                        this.generatePlaceholder(this.PLACEMENT_ID.MINICART)
                    );
                if (this.PLACEMENT_ID.MINICART in this.paymentRequestByPlacement) {
                    const container = document.getElementById(
                        this.getElementUniqueId(this.PLACEMENT_ID.MINICART, 'container')
                    );
                    if (container) {
                        container.style.display = 'block'; // Show the container
                    }
                    this.checkMountElementHealth(this.PLACEMENT_ID.MINICART, () => {
                        this.createExpressCheckoutElement(
                            this.PLACEMENT_ID.MINICART,
                            this.paymentRequestByPlacement[this.PLACEMENT_ID.MINICART].paymentRequestOptions,
                            this.paymentRequestByPlacement[this.PLACEMENT_ID.MINICART].checkoutOptions,
                            this.paymentRequestByPlacement[this.PLACEMENT_ID.MINICART].elementsOptions
                        );
                    });
                }
                }
            );
        }
    },
    async cancelOrder(placementId, orderId, orderIncrementId, errorMessage) {
        let error;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.expressCheckoutElement.controllers.cancel);
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
                console.error('There was a problem while fetching express checkout cancelOrder:', error);
            });

        // if (error) {
        //     paymentelementFailsafe.initFailsafePaymentRequest(this.stripe, errorMessage);
        // }
    },
    failProofOptions(options, checkoutOptions) {
        let lineItemsTotal = 0;
        if (checkoutOptions && checkoutOptions.lineItems) {
            for (const lineItem of checkoutOptions.lineItems) {
                lineItemsTotal += Number(lineItem.amount);
            }
        }
        if (options && options.amount && Number(options.amount) < lineItemsTotal) {
            options.amount = lineItemsTotal
        }
        return options;
    },
    failproofCheckoutOptions(placementId, options, currentTotal) {
        try {
            if (!currentTotal) {
                return options;
            }
            const sum = options.lineItems.reduce((acc, {amount = 0}) => acc + amount, 0);
            if (sum !== currentTotal) {
                options.lineItems = [{name: 'Grand Total', amount: currentTotal}];
                console.log('[' + placementId + '] Brippo express checkout options were amended.');
            }
        } catch (e) {
            console.error('Brippo: unable to failproofCheckoutOptions', e);
        }
        return options;
    },
    pickupInputValues() {
        const response = [];
        const pickupConfigText = BrippoPayments.config.expressCheckoutElement.pickupInputValues;
        let config;
        try {
            config = JSON.parse(pickupConfigText);
        } catch (e) {
            console.error('Invalid JSON in pickupInputValues config:', e);
            return response;
        }

        try {
            const getOrdinal = day => {
                if (day > 3 && day < 21) return 'th';
                switch (day % 10) {
                    case 1: return 'st';
                    case 2: return 'nd';
                    case 3: return 'rd';
                    default: return 'th';
                }
            };

            config.forEach(({ name, inputSelector, type }) => {
                const input = document.querySelector(inputSelector);
                if (!input || !input.value) return;

                let value = input.value;

                if (type === 'date') {
                    const date = new Date(value);
                    const day = date.getDate();
                    const suffix = getOrdinal(day);
                    const weekday = new Intl.DateTimeFormat('en-GB', { weekday: 'long' }).format(date);
                    const month   = new Intl.DateTimeFormat('en-GB', { month:   'long' }).format(date);
                    const year    = date.getFullYear();
                    value = `${weekday} ${day}${suffix} ${month} ${year}`;
                }

                response.push({ name, value });
            });
        } catch (e) {
            console.error('Unable to parse pickupInputValues', e);
            return response;
        }

        return response;
    },
    keepRegeneratingIfGone(placementId) {
        setTimeout(() => {
            if (!document.getElementById(this.getElementUniqueId(placementId, 'mount'))) {
                console.log('[' + placementId + '] Brippo express checkout was removed. Regenerating...');
                this.lastTotalRequestedByPlacement[placementId] = null;
                BrippoPayments.waitForElementToExists(
                    this.getPlacementSelector(placementId),
                    () => {
                        this.insertPlaceholder(
                            placementId,
                            this.generatePlaceholder(placementId)
                        );
                        this.initializeExpressCheckout(placementId).then(() => {
                            console.log('[' + placementId + '] Brippo express checkout was regenerated.');
                        });
                    }
                );
            }
            this.keepRegeneratingIfGoneCounter++
            if (this.keepRegeneratingIfGoneCounter < 20) {
                this.keepRegeneratingIfGone(placementId);
            }
        }, 2000);
    },
    getSelectedDeliveryOptionFromCart(placementId) {
        // Only try to get selected delivery option when on cart page
        if (placementId !== this.PLACEMENT_ID.CART
            && placementId !== this.PLACEMENT_ID.MINICART
            && placementId !== this.PLACEMENT_ID.CHECKOUT_TOP) {
            return null;
        }

        try {
            // Try to access Magento's quote and checkout data if already loaded
            if (typeof window.require !== 'undefined' && window.require.defined) {
                try {
                    // Check if modules are already loaded
                    if (window.require.defined('Magento_Checkout/js/model/quote')) {
                        const quote = window.require('Magento_Checkout/js/model/quote');
                        if (quote && quote.shippingMethod && quote.shippingMethod()) {
                            const method = quote.shippingMethod();
                            if (method.carrier_code && method.method_code) {
                                const selectedMethod = method.carrier_code + '_' + method.method_code;
                                console.log('[' + placementId + '] Found selected delivery option from quote: ' + selectedMethod);
                                return selectedMethod;
                            }
                        }
                    }

                    if (window.require.defined('Magento_Checkout/js/checkout-data')) {
                        const checkoutData = window.require('Magento_Checkout/js/checkout-data');
                        if (checkoutData && checkoutData.getSelectedShippingRate) {
                            const shippingRate = checkoutData.getSelectedShippingRate();
                            if (shippingRate && shippingRate.carrier_code && shippingRate.method_code) {
                                const selectedMethod = shippingRate.carrier_code + '_' + shippingRate.method_code;
                                console.log('[' + placementId + '] Found selected delivery option from checkout data: ' + selectedMethod);
                                return selectedMethod;
                            }
                        }
                    }
                } catch (error) {
                    console.error('[' + placementId + '] Error accessing loaded Magento modules:', error);
                }
            }

            // Fallback: Try to find selected shipping method from DOM selectors
            const shippingMethodSelectors = [
                'input[name="shipping_method"]:checked',
                'input[type="radio"][name*="shipping"]:checked',
                '.table-checkout-shipping-method input:checked',
                '.shipping-method input:checked',
                '[data-role="shipping-method"] input:checked'
            ];

            for (const selector of shippingMethodSelectors) {
                const selectedElement = document.querySelector(selector);
                if (selectedElement && selectedElement.value) {
                    console.log('[' + placementId + '] Found selected delivery option from DOM: ' + selectedElement.value);
                    return selectedElement.value;
                }
            }

            // Another fallback: try to get from shipping method table rows
            const shippingRows = document.querySelectorAll('.table-checkout-shipping-method tbody tr');
            for (const row of shippingRows) {
                const radioInput = row.querySelector('input[type="radio"]');
                if (radioInput && radioInput.checked) {
                    console.log('[' + placementId + '] Found selected delivery option from table row: ' + radioInput.value);
                    return radioInput.value;
                }
            }

            // Final fallback: check for selected shipping method in global checkout config
            if (typeof window.checkoutConfig !== 'undefined' &&
                window.checkoutConfig.selectedShippingMethod) {
                const selectedMethod = window.checkoutConfig.selectedShippingMethod;
                const methodId = selectedMethod.carrier_code + '_' + selectedMethod.method_code;
                console.log('[' + placementId + '] Found selected delivery option from global checkout config: ' + methodId);
                return methodId;
            }

            console.log('[' + placementId + '] No selected delivery option found on cart page');
            return null;
        } catch (error) {
            console.error('[' + placementId + '] Error getting selected delivery option from cart:', error);
            return null;
        }
    },
    setCheckoutBillingAddress(billingAddress) {
        this.checkout.billingAddress = {
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
    setCheckoutShippingAddress(shippingAddress, shippingDetails) {
        this.checkout.shippingAddress = {
            firstname: shippingAddress.firstname,
            lastname: shippingAddress.lastname,
            street: shippingAddress.street,
            city: shippingAddress.city,
            countryId: shippingAddress.countryId,
            postcode: shippingAddress.postcode,
            telephone: shippingAddress.telephone,
            region: shippingAddress.region,
            regionId: shippingAddress.regionId
        };

        this.checkout.shippingDetails = shippingDetails;
    },
    setCheckoutShippingMethod(quote) {
        this.checkout.shippingMethod = null;
        try {
            this.checkout.shippingMethod = quote.shippingMethod();
        } catch (e) {
            console.log(e);
        }
    },
    setCheckoutEmail(email) {
        this.checkout.email = email;
    }
}

document.addEventListener('onBrippoPaymentsInitializationComplete', () => {
    window.brippoExpressCheckoutElement.onBrippoPaymentsInitialized();
});
window.brippoExpressCheckoutElement.initialize().then();
window.brippoExpressCheckoutElement.EVENTS.beforePlaceOrder = new CustomEvent('brippoExpressCheckoutElement_beforePlaceOrder', {});

if (typeof require === 'function') {
    require([
        'Magento_Customer/js/customer-data'
    ], function (customerData) {
        if (customerData) {
            window.brippoExpressCheckoutElement.invalidateCart = () => {
                customerData.invalidate(['cart']);
                customerData.reload(['cart'], true);
            }

            let cart = customerData.get('cart');
            cart.subscribe(() => {
                setTimeout(window.brippoExpressCheckoutElement.onCartTotalUpdated, 500);
            });
        }
    });
}
