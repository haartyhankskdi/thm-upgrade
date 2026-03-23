define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'mage/url',
        'Magento_Customer/js/customer-data',
        'brippo_failsafe_paymentelement',
        'Magento_Ui/js/modal/modal'
    ],
    function ($, alert, url, customerData, paymentelementFailsafe, modal) {
        "use strict";
        return {
            buttonType: "",
            buttonTheme: "",
            buttonHeight: "",
            currentProductId: "",
            stripe: null,
            onButtonReadyHandler: null,
            justAddedToCart: false,
            onCanMakePaymentHandlers: [],
            isCouponCodeFeatureEnabled: false,
            placeOrderLock: false,
            canMakePayment: null,
            lastTotalRequested: {},
            appliedCouponCode: null,
            paymentRequestsPerSource: {},
            discountInputsPerSource: {},
            shippingAddress: null,
            beforePlaceOrderEvent: null,
            initPaymentRequest: function (config, source, onButtonReadyHandler, callback) {

                if (this.justAddedToCart) {
                    this.justAddedToCart = false;
                    return;
                }

                const self = this;
                this.buttonType = config.buttonType;
                this.buttonTheme = config.buttonTheme;
                this.buttonHeight = config.buttonHeight;
                this.isCouponCodeFeatureEnabled = config.enabledCouponCode;
                if (config.isEnabled) {
                    this.onButtonReadyHandler = onButtonReadyHandler;

                    if (!this.stripe) {
                        this.stripe = Stripe(config.pKey, {
                            apiVersion: "2020-08-27"
                        });

                        this.beforePlaceOrderEvent = new CustomEvent('brippoExpress_beforePlaceOrderEvent', {});
                    }

                    this.resetPaymentErrors(source);

                    self.getPaymentRequestOptions(source, function (response) {
                        if (response.valid === 0) {
                            self.stopButtonLoader(source);
                            self.showPaymentError(response.message, source);
                        } else if (response.valid === 1) {
                            //disable for 0 amount quotes
                            const amount = parseFloat(response.options.total.amount);
                            if (!self.isProductPage(source)) {
                                if (
                                    amount <= 0 ||
                                    (config.thresholdMinimum && Number(config.thresholdMinimum) > 0 && amount < Number(config.thresholdMinimum)) ||
                                    (config.thresholdMaximum && Number(config.thresholdMaximum) > 0 && amount > Number(config.thresholdMaximum))
                                ) {
                                    if (self.isMinicart(source) || self.isCart(source)) {
                                        $('.scExpressSeparator').hide();
                                    }
                                    self.stopButtonLoader(source);
                                    return;
                                }
                            }

                            //avoid re-requesting and button flashing for same amount
                            if (self.lastTotalRequested &&
                                source.source in self.lastTotalRequested &&
                                self.lastTotalRequested[source.source] === response.options.total.amount) {
                                self.stopButtonLoader(source);
                                return;
                            }
                            self.lastTotalRequested[source.source] = response.options.total.amount;

                            if (self.isCouponCodeFeatureEnabled && response.coupon && response.coupon !== '') {
                                self.autofillDiscountInputs(response.coupon);
                            }

                            self.createPaymentRequest(
                                response.options,
                                source,
                                function (canMakePayment, paymentRequest) {
                                    if (callback) {
                                        callback(canMakePayment, paymentRequest);
                                    }
                                }
                            );

                            BrippoPayments.reportAnalytic('magento2_payment_request_button_' + source.source);
                        }
                    });
                }
            },
            getPaymentRequestOptions: function (definedSource, callback) {
                const self = this;

                if (window.checkout && window.checkout.baseUrl !== undefined) {
                    url.setBaseUrl(window.checkout.baseUrl);
                }

                self.startButtonLoader(definedSource);
                let error;
                try {
                    $.ajax({
                        url: url.build('brippo_payments/expresscheckout/paymentrequest'),
                        data: {
                            'source': definedSource.source,
                            'currentProductId': definedSource.currentProductId
                        },
                        type: 'POST',
                        dataType: 'json',
                        showLoader: false
                    }).fail(function (jqXHR) {
                        error = self.handleAjaxFail(jqXHR);
                        self.showPaymentError(error, definedSource);
                    }).done(function (response) {
                        callback(response);
                        console.log('Brippo: Express Checkout payment request completed for ' + response.options.total.amount + '.')
                    });
                } catch (ajaxError) {
                    console.log(ajaxError);
                }
            },
            createPaymentRequest: function (options, definedSource, callback) {
                const self = this;
                let paymentRequest = this.stripe.paymentRequest(options);
                if (!(definedSource.source in this.paymentRequestsPerSource)) {
                    this.paymentRequestsPerSource[definedSource.source] = paymentRequest;
                }

                let elements = this.stripe.elements();
                let prButton = elements.create('paymentRequestButton', {
                    paymentRequest: paymentRequest,
                    style: {
                        paymentRequestButton: {
                            type: this.buttonType,
                            theme: this.buttonTheme,
                            height: this.buttonHeight
                        }
                    }
                });

                paymentRequest.canMakePayment().then(function (result) {
                    let canMakePaymentResult = result;
                    self.canMakePayment = canMakePaymentResult;
                    if (canMakePaymentResult) {
                        paymentRequest.on('paymentmethod', function (ev) {
                            self.onPaymentMethod(ev, definedSource);
                        });
                        paymentRequest.on('shippingoptionchange', function (ev) {
                            self.onShippingOptionChange(ev, definedSource);
                        });
                        paymentRequest.on('shippingaddresschange', function (ev) {
                            self.onShippingAddressChange(ev, definedSource);
                        });
                        paymentRequest.on('cancel', function (ev) {
                            self.onCancel(ev, definedSource);
                        });

                        if (!self.isCustomButtonCheckout(definedSource)) {
                            prButton.on('click', function (ev) {
                                self.onPaymentButtonClick(ev, definedSource, paymentRequest, canMakePaymentResult);
                            });
                            prButton.on('loaderror', function (ev) {
                                self.onPaymentButtonLoadError(ev, definedSource, paymentRequest, canMakePaymentResult);
                            });
                            prButton.mount('#' + definedSource.elementId);
                            prButton.on('ready', function () {
                                if (self.onButtonReadyHandler) {
                                    self.onButtonReadyHandler(prButton);
                                }
                            });
                            self.enableButtons(definedSource);
                        }

                        for (let i = 0; i < self.onCanMakePaymentHandlers.length; i++) {
                            self.onCanMakePaymentHandlers[i]();
                        }
                        if (callback) {
                            callback(true, paymentRequest);
                        }
                    } else {
                        if (callback) {
                            callback(false, null);
                        }
                    }
                });
            },
            startButtonLoader: function (definedSource) {
                const buttonElement = $('#' + definedSource.elementId);
                if (buttonElement.length && !$('#' + definedSource.elementId + ' .brippoOverLoader').length) {
                    buttonElement.append('<div class="brippoOverLoader"><div class="brippoOverLoaderSpinner"></div></div>');
                }
            },
            stopButtonLoader: function (definedSource) {
                if ($('#' + definedSource.elementId).length && $('#' + definedSource.elementId + ' .brippoOverLoader').length) {
                    $('#' + definedSource.elementId + ' .brippoOverLoader').remove();
                }
            },
            onPaymentMethod: function (ev, definedSource) {
                const self = this;

                if (self.placeOrderLock === true) {
                    return;
                }
                self.placeOrderLock = true;

                self.placeOrder(ev, definedSource, function (placeOrderResponse) {
                    self.placeOrderLock = false;
                    if (placeOrderResponse.valid === 0) {
                        ev.complete('success');
                        self.showPaymentError(placeOrderResponse.message, definedSource);
                    } else if (placeOrderResponse.valid === 1) {
                        try {
                            self.stripe.confirmCardPayment(
                                placeOrderResponse['client_secret'],
                                {payment_method: ev.paymentMethod.id},
                                {handleActions: false}
                            ).then(async function (confirmResult) {
                                let doubleCheckEnabled = true;
                                // testing
                                if (typeof EbizmartsDEV_confirmResult !== 'undefined') {
                                    confirmResult = EbizmartsDEV_confirmResult;
                                    doubleCheckEnabled = (typeof confirmResult.doubleCheck !== 'undefined');
                                }

                                // If there's an error, check the payment intent status (pi_status)
                                if (confirmResult.error) {
                                    const pi_status = await self.checkPaymentStatus(placeOrderResponse['payment_intent_id']);

                                    if (doubleCheckEnabled && (pi_status === "succeeded" || pi_status === "requires_action")) {
                                        self.log(
                                            "Confirm failed but payment succeeded" ,
                                            {
                                                pi: placeOrderResponse['payment_intent_id'],
                                                error: confirmResult ?? []
                                            }
                                        );

                                        self.handleSuccessfulConfirmation(
                                            placeOrderResponse,
                                            definedSource,
                                            pi_status,
                                            confirmResult.error.message,
                                            ev
                                        );
                                    } else {
                                        ev.complete('success');
                                        self.log(confirmResult.error.message, {
                                            definedSource: definedSource,
                                            response: confirmResult.error
                                        });
                                        self.cancelOrder(
                                            placeOrderResponse['order_id'],
                                            definedSource,
                                            confirmResult.error.message
                                        );
                                    }
                                } else {
                                    // If no error, proceed to handle success
                                    self.handleSuccessfulConfirmation(
                                        placeOrderResponse,
                                        definedSource,
                                        confirmResult.paymentIntent.status,
                                        confirmResult.error?.message ?? "",
                                        ev
                                    );
                                }
                            });
                        } catch (error) {
                            ev.complete('fail');
                            self.log('Error confirming payment.', {
                                definedSource: definedSource,
                                response: error
                            });
                        }
                    }
                });
            },
            handleSuccessfulConfirmation: function (placeOrderResponse, definedSource, confirmStatus, confirmError, ev) {
                const self = this;

                ev.complete('success');
                if (confirmStatus === "requires_action") {
                    self.stripe.confirmCardPayment(placeOrderResponse['client_secret']).then(function (result) {
                        if (result.error) {
                            self.cancelOrder(
                                placeOrderResponse['order_id'],
                                definedSource,
                                result.error.message ? result.error.message : confirmError
                            );
                        } else {
                            self.completeOrder(placeOrderResponse, definedSource, result.paymentIntent.status);
                        }
                    });
                } else {
                    self.completeOrder(placeOrderResponse, definedSource, confirmStatus);
                }
            },
            checkPaymentStatus: async function (paymentIntentId) {
                let status = null;
                let error = null;
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
                    }).done(function (response) {
                        if (response.valid === 1) {
                            status = response.status;
                        }
                    });
                } catch (ex) {
                    console.log(ex.message);
                    return false;
                }

                return status;
            },
            onShippingOptionChange: function (ev, definedSource) {
                console.log('onShippingOptionChange');
                const self = this;

                $.ajax({
                    url: url.build('brippo_payments/expresscheckout/addshippingmethod'),
                    data: {
                        'shippingOption': ev.shippingOption
                    },
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true
                }).fail(function (response) {
                    let error = self.handleAjaxFail(response);
                    self.showPaymentError(error, definedSource);
                    self.log('Error on shipping option change.', {
                        definedSource: definedSource,
                        response: error
                    });
                }).done(function (response) {
                    ev.updateWith(response.updateDetails);
                    if (response.valid === 0) {
                        self.showPaymentError(response.message, definedSource);
                    }
                });
            },
            onShippingAddressChange: function (ev, definedSource) {
                console.log('onShippingAddressChange');
                const self = this;
                self.shippingAddress = ev.shippingAddress;

                $.ajax({
                    url: url.build('brippo_payments/expresscheckout/addshippingaddress'),
                    data: {
                        'shippingAddress': ev.shippingAddress
                    },
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true
                }).fail(function (response) {
                    console.log(response);
                    self.showPaymentError(self.handleAjaxFail(response), definedSource);
                    self.log('Error on shipping address change.', {
                        definedSource: definedSource,
                        response: self.handleAjaxFail(response)
                    });
                }).done(function (response) {
                    ev.updateWith(response.updateDetails);
                    if (response.valid === 0) {
                        self.showPaymentError(response.message, definedSource);
                    }
                });
            },
            onCancel: function (ev, definedSource) {
                $('body').trigger('processStop');

                customerData.invalidate(['cart']);
                customerData.reload(['cart'], true);
            },
            onPaymentButtonClick: async function (ev, definedSource, paymentRequest, canMakePaymentResult) {
                if ($('.brippoExpressAgreements.' + definedSource.source).length) {
                    if (!$('.brippoExpressAgreements.' + definedSource.source + ' input[type=checkbox]').prop('checked')) {
                        this.showPaymentError("Please agree to the terms and conditions before placing the order.", definedSource);
                        $('.brippoExpressAgreements.' + definedSource.source + ' label').css('color', '#e02b27');
                        ev.preventDefault();
                        return;
                    } else {
                        this.resetPaymentErrors(definedSource);
                        $('.brippoExpressAgreements.' + definedSource.source + ' label').css('color', '#000');
                    }
                }

                if (this.isProductPage(definedSource)) {
                    const {error: validateError, request} = this.validateProductPageSelection();
                    if (validateError) {
                        ev.preventDefault();
                        return;
                    }

                    // We don't want to preventDefault for applePay because we cannot use paymentRequest.show().
                    if (!canMakePaymentResult.applePay) {
                        ev.preventDefault();
                    }

                    $(definedSource.elementId).addClass('disabled');
                    const {error: addToCartError, addToCartResponse} = await this.addToCart(request, definedSource)
                    $(definedSource.elementId).removeClass('disabled');

                    if (!addToCartError) {
                        if (!canMakePaymentResult.applePay) {
                            /**
                             * Since apple pay flow continued and the modal is already displayed, we can not update the request.
                             * We can only relay in the shipping address change event to update total.
                             */
                            try {
                                this.paymentRequestsPerSource[definedSource.source].update(addToCartResponse.options);
                                console.log('Brippo: Express Checkout payment request updated for ' + addToCartResponse.options.total.amount + '.')
                                this.paymentRequestsPerSource[definedSource.source].show();
                            } catch (err) {
                                console.log(err);
                            }
                        }
                    }
                }
            },
            onPaymentButtonLoadError: function (ev, definedSource, paymentRequest, canMakePaymentResult) {
                const self = this;
                self.log('Payment button load error.', {
                    definedSource: definedSource,
                    response: ev
                });
            },
            applyCouponCode: async function (couponCode, definedSource) {
                const self = this;
                let error;
                let addCouponResponse;
                this.resetPaymentErrors(definedSource);
                let productPageFormRequest;

                if (!couponCode || couponCode === '') {
                    error = 'Invalid coupon code.';
                    this.showPaymentError(error, definedSource);
                    return { error };
                }

                if (this.isProductPage(definedSource)) {
                    const {error: validateError, request} = this.validateProductPageSelection();

                    if (validateError) {
                        error = 'Please select product options.';
                        return {error};
                    }

                    productPageFormRequest = request;
                }

                this.startButtonLoader(definedSource);
                try {
                    await $.ajax({
                        url: url.build('brippo_payments/expresscheckout/addcoupon'),
                        data: {
                            request: productPageFormRequest,
                            code: couponCode,
                            definedSource: definedSource,
                            shippingAddress: self.shippingAddress
                        },
                        type: 'POST',
                        dataType: 'json',
                        showLoader: true
                    }).fail(function (jqXHR) {
                        error = self.handleAjaxFail(jqXHR);
                    }).done(function (response) {
                        if (response.valid === 0) {
                            error = response.message;
                        } else {
                            console.log('Brippo: Coupon code applied successfully');
                            addCouponResponse = response
                        }
                    });
                } catch (ajaxError) {
                    console.log(ajaxError);
                }

                this.stopButtonLoader(definedSource);
                if (error) {
                    this.showPaymentError(error, definedSource);
                } else {
                    this.paymentRequestsPerSource[definedSource.source].update(addCouponResponse.options);
                    console.log('Brippo: Express Checkout payment request updated for ' + addCouponResponse.options.total.amount + '.')
                    this.appliedCouponCode = couponCode;
                    //don't update frontend cart as it might trigger minicart animations during payment modal display
                    // customerData.invalidate(['cart']);
                    // customerData.reload(['cart'], true);
                }

                return { error };
            },
            validateProductPageSelection: function () {
                const form = $('#product_addtocart_form');
                let request = [];
                let error;
                const validator = form.validation({radioCheckboxClosest: '.nested'});

                if (!validator.valid()) {
                    error = true;
                }

                request = form.serialize();

                return { error, request }
            },
            addToCart: async function (request, definedSource) {
                const self = this;
                this.resetPaymentErrors(definedSource);
                let error;
                let addToCartResponse;
                try {
                    await $.ajax({
                        url: url.build('brippo_payments/expresscheckout/addtocart'),
                        data: {
                            request: request,
                            shippingAddress: self.shippingAddress
                        },
                        type: 'POST',
                        dataType: 'json',
                        showLoader: true
                    }).fail(function (jqXHR) {
                        error = self.handleAjaxFail(jqXHR);
                    }).done(function (response) {
                        if (response.valid === 0) {
                            error = response.message;
                        } else {
                            addToCartResponse = response
                        }
                    });
                } catch (ajaxError) {
                    console.log(ajaxError);
                }

                if (error) {
                    $('body').trigger('processStop');
                    this.showPaymentError(error, definedSource);
                } else {
                    //don't update frontend cart as it might trigger minicart animations during payment modal display
                    // customerData.invalidate(['cart']);
                    // customerData.reload(['cart'], true);
                    this.justAddedToCart = true;
                }

                return { error, addToCartResponse }
            },
            enableButtons: function (definedSource) {
                $('#' + definedSource.elementId).css('display', 'block');
                $('.scExpressSeparator').css('display', 'flex');
                $('.expressAddCoupon .content').css('display', 'block');
                $('.brippoExpressAgreements').css('display', 'block');
            },
            showPaymentError: function (message, definedSource) {
                if (definedSource === undefined) {
                    console.log("Error: " + message);
                    return;
                }
                let span = document.getElementById(definedSource.elementId + '-errors');
                span.innerHTML = '<span>' + message + '</span>';
                span.style.display="block";
            },
            resetPaymentErrors: function (definedSource) {
                let span = document.getElementById(definedSource.elementId + '-errors');
                if (span) {
                    span.style.display = "none";
                }
            },
            fillMissingDataForPlaceOrder: function (paymentData, definedSource) {
                if (this.isCheckoutSource(definedSource)) {
                    if (window.brippo_quote_billing_address &&
                        !window.brippo_quote_billing_address['telephone']) {
                        window.brippo_quote_billing_address['telephone'] = paymentData.payerPhone;
                    }
                    if (window.brippo_quote_shipping_address &&
                        !window.brippo_quote_shipping_address['telephone']) {
                        window.brippo_quote_shipping_address['telephone'] = paymentData.payerPhone;
                    }
                }
            },
            placeOrder: function (paymentData, definedSource, callback) {
                document.dispatchEvent(this.beforePlaceOrderEvent);
                this.fillMissingDataForPlaceOrder(paymentData, definedSource);
                $.ajax({
                    url: url.build('brippo_payments/expresscheckout/placeorder'),
                    data: {
                        payerName: paymentData.payerName,
                        payerPhone: paymentData.payerPhone,
                        payerEmail: paymentData.payerEmail,
                        checkoutEmail: window.brippo_quote_email,
                        shippingAddress: paymentData.shippingAddress,
                        shippingMethod: window.brippo_quote_shipping_method,
                        billingDetails: paymentData.paymentMethod.billing_details,
                        source: definedSource.source,
                        card: paymentData.paymentMethod.card,
                        provider: this.getWalletName(),
                        billingAddress: window.brippo_quote_billing_address,
                        checkoutShippingAddress: window.brippo_quote_shipping_address
                    },
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true
                }).done((data) => {
                    if (callback) {
                        callback(data);
                    }
                }).fail((response) => {
                    if (callback) {
                        callback({
                            "valid": 0,
                            "message": this.handleAjaxFail(response)
                        });
                    }
                    this.log('Error placing order.', {
                        definedSource: definedSource,
                        response: this.handleAjaxFail(response)
                    });
                });
            },
            log: function (message, data) {
                const self = this;
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
            completeOrder: function (data, definedSource, status) {
                const self = this;
                $.ajax({
                    url: url.build('brippo_payments/expresscheckout/complete'),
                    data: {
                        'orderId': data['order_id'],
                        'orderIncrementId': data['order_increment_id'],
                        'paymentIntentId': data['payment_intent_id'],
                        'status': status
                    },
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true
                }).done(function (response) {
                    customerData.invalidate(['cart']);
                    customerData.reload(['cart'], true);
                    location.href = url.build('checkout/onepage/success');
                }).fail(function (response) {
                    console.log(self.handleAjaxFail(response));
                    self.showPaymentError(self.handleAjaxFail(response), definedSource);
                    self.log('Error completing order.', {
                        definedSource: definedSource,
                        response: self.handleAjaxFail(response)
                    });
                });
            },
            cancelOrder: function (orderId, definedSource, errorMessage) {
                const self = this;
                $.ajax({
                    url: url.build('brippo_payments/expresscheckout/cancel'),
                    data: {
                        'orderId': orderId,
                        'error': errorMessage
                    },
                    type: 'GET',
                    dataType: 'json',
                    showLoader: true
                }).done(function (result) {
                    if (result.valid === 0) {
                        self.log('Error canceling order.', {
                            definedSource: definedSource,
                            response: result
                        });
                    }
                    paymentelementFailsafe.initFailsafePaymentRequest(self.stripe, errorMessage);
                }).fail(function (response) {
                    console.log(self.handleAjaxFail(response));
                    self.log('Error canceling order.', {
                        definedSource: definedSource,
                        response: response
                    });
                    paymentelementFailsafe.initFailsafePaymentRequest(self.stripe, errorMessage);
                });
            },
            isCustomButtonCheckout: function (definedSource) {
                return this.isCheckoutSource(definedSource) &&
                    window.checkoutConfig.payment.stripeconnect_express &&
                    window.checkoutConfig.payment.stripeconnect_express.checkoutButton === 'default' &&
                    window.checkoutConfig.payment.stripeconnect_express.checkoutLocation !== 'on_top';
            },
            isCheckoutSource: function (definedSource) {
                return definedSource.source === 'checkout';
            },
            isMinicart: function (definedSource) {
                return definedSource.source === 'minicart';
            },
            isCart: function (definedSource) {
                return definedSource.source === 'cart';
            },
            isProductPage: function (definedSource) {
                return definedSource.source === 'product_page';
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
            isGooglePay: function () {
                return this.canMakePayment && this.canMakePayment.googlePay === true;
            },
            isApplePay: function () {
                return this.canMakePayment && this.canMakePayment.applePay === true;
            },
            isLink: function () {
                return this.canMakePayment && this.canMakePayment.link === true;
            },
            getWalletName: function () {
                if (this.isGooglePay()) {
                    return "Google Pay";
                } else if (this.isApplePay()) {
                    return "Apple Pay";
                } else if (this.isLink()) {
                    return "Link";
                }
                return "";
            },
            showAgreementsModal: function (source, agreementId) {
                const options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    buttons: []
                };

                const modalElement = $('#brippoAgreementsModal_' + source + agreementId);
                modal(options, modalElement);
                modalElement.modal("openModal");
            },
            removeCouponCode: async function (definedSource) {
                let error;
                let removeCouponResponse;
                const self = this;
                this.startButtonLoader(definedSource);
                try {
                    await $.ajax({
                        url: url.build('brippo_payments/expresscheckout/removecoupon'),
                        type: 'POST',
                        data: {
                            shippingAddress: this.shippingAddress
                        },
                        dataType: 'json',
                        showLoader: true
                    }).fail(function (jqXHR) {
                        error = self.handleAjaxFail(jqXHR);
                    }).done(function (response) {
                        if (response.valid === 0) {
                            error = response.message;
                        } else {
                            console.log('Brippo: Coupon code removed successfully');
                            removeCouponResponse = response
                        }
                    });
                } catch (ajaxError) {
                    console.log(ajaxError);
                }

                this.stopButtonLoader(definedSource);
                if (error) {
                    this.showPaymentError(error, definedSource);
                } else {
                    this.paymentRequestsPerSource[definedSource.source].update(removeCouponResponse.options);
                    console.log('Brippo: Express Checkout payment request updated for ' + removeCouponResponse.options.total.amount + '.')
                    this.appliedCouponCode = null;
                }

                return { error };
            },
            registerDiscountInput: function (addDiscountDropdown, inputContainer, applyCouponButton, removeCouponButton, input, definedSource, addCouponSuccess, addCouponNote) {
                console.log('Brippo: Registering discount input for ' + definedSource.source + '...');
                const self = this;

                this.discountInputsPerSource[definedSource.source] = {
                    addDiscountDropdown: addDiscountDropdown,
                    inputContainer: inputContainer,
                    applyCouponButton: applyCouponButton,
                    removeCouponButton: removeCouponButton,
                    input: input,
                    addCouponSuccess: addCouponSuccess,
                    addCouponNote: addCouponNote
                }

                addDiscountDropdown.addEventListener('click', function () {
                    addDiscountDropdown.style.display = 'none';
                    applyCouponButton.style.display = 'block';
                    removeCouponButton.style.display = 'none';
                    inputContainer.style.display = 'flex';
                });

                applyCouponButton.addEventListener('click', async function () {
                    applyCouponButton.disabled = true;
                    input.disabled = true;
                    const {error} = await self.applyCouponCode(input.value, definedSource);
                    if (!error) {
                        applyCouponButton.style.display = 'none';
                        removeCouponButton.style.display = 'block';
                        addCouponSuccess.style.display = 'block';
                        if (addCouponNote) {
                            addCouponNote.style.display = 'none';
                        }
                    } else {
                        applyCouponButton.disabled = false;
                        input.disabled = false;
                    }
                });

                removeCouponButton.addEventListener('click', async function () {
                    removeCouponButton.disabled = true;
                    const {error} = await self.removeCouponCode(definedSource);
                    if (!error) {
                        applyCouponButton.style.display = 'block';
                        removeCouponButton.style.display = 'none';
                        addCouponSuccess.style.display = 'none';
                        if (addCouponNote) {
                            addCouponNote.style.display = 'block';
                        }
                        input.disabled = false;
                        input.value = '';
                        applyCouponButton.disabled = false;
                    } else {
                        removeCouponButton.disabled = false;
                    }
                });
            },
            autofillDiscountInputs: function (couponCode) {
                console.log('Brippo: Auto-filling discount inputs...');
                for (const [key, value] of Object.entries(this.discountInputsPerSource)) {
                    value.addDiscountDropdown.style.display = 'none';
                    value.inputContainer.style.display = 'flex';
                    value.applyCouponButton.style.display = 'none';
                    value.removeCouponButton.style.display = 'block';
                    value.addCouponSuccess.style.display = 'block';
                    if (value.addCouponNote) {
                        value.addCouponNote.style.display = 'none';
                    }
                    value.input.value = couponCode;
                    value.input.disabled = true;
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
                } else {
                    if (jqXHR.status) {
                        error += '. Status code: ' + jqXHR.status;
                    }
                }

                return error;
            },
            setQuoteBillingAddressForPlaceOrder: function (billingAddress) {
                window.brippo_quote_billing_address = {
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
            setQuoteShippingAddressForPlaceOrder: function (shippingAddress) {
                window.brippo_quote_shipping_address = {
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
            setQuoteShippingMethodForPlaceOrder: function (quote) {
                window.brippo_quote_shipping_method = null;
                try {
                    window.brippo_quote_shipping_method = quote.shippingMethod();
                } catch (e) {
                    console.log(e);
                }
            },
            setQuoteEmailForPlaceOrder: function (email) {
                window.brippo_quote_email = email;
            }
        };
    }
);
