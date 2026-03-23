define(
    [
        'jquery',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/modal/modal'
    ],
    function (
        $,
        url,
        fullScreenLoader,
        modal
    ) {
        'use strict';
        return {
            stripe: null,
            elements: null,
            initFailsafePaymentRequest: async function (stripe, originalErrorMessage) {
                if (this.stripe !== null) {
                    return;
                }

                $('#brippo-failsafe-paymentelement-pay')
                    .unbind()
                    .click(() => {
                        this.pay();
                    })
                    .prop('disabled', false);

                this.stripe = stripe;
                const {error, paymentRequestOptions} = await this.getPaymentRequestOptions();
                if (error) {
                    this.showPaymentError(response.message);
                    return;
                }

                this.initializePaymentElement(paymentRequestOptions);

                let errorToDisplay = originalErrorMessage;
                if (errorToDisplay && errorToDisplay.slice(-1) !== '.') {
                    errorToDisplay += '.';
                }
                if (!errorToDisplay || !originalErrorMessage.includes('Please choose a different payment method and try again')) {
                    errorToDisplay += ' Please choose a different payment method and try again.';
                }

                this.showPaymentError(errorToDisplay);
                this.showModal();
            },
            showModal: function () {
                const options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    buttons: []
                };

                modal(options, $('#brippoFailsafePaymentElementModal'));
                $("#brippoFailsafePaymentElementModal").modal("openModal");
            },
            async getPaymentRequestOptions() {
                if (window.checkout && window.checkout.baseUrl !== undefined) {
                    url.setBaseUrl(window.checkout.baseUrl);
                }

                let error, paymentRequestOptions;
                try {
                    await $.ajax({
                        url: url.build('brippo_payments/paymentelement/paymentrequest'),
                        type: 'POST',
                        dataType: 'json',
                        showLoader: true
                    }).fail((jqXHR) => {
                        error = this.handleAjaxFail(jqXHR);
                    }).done((response) => {
                        paymentRequestOptions = response.options;
                    });
                } catch (ex) {
                    console.log(ex.message);
                }

                return {error, paymentRequestOptions}
            },
            initializePaymentElement: function (elementsOptions) {
                this.elements = this.stripe.elements(elementsOptions);
                const paymentElement = this.elements.create("payment", {});
                paymentElement.mount("#brippo-failsafe-paymentelement");
            },
            log: function (message, data) {
                try {
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
                } catch (ex) {
                    console.log(ex.message);
                }
            },
            showPaymentError: function (message) {
                let span = document.getElementById('brippo-failsafe-paymentelement-errors');
                let finalMessage = message;
                if (typeof finalMessage === 'object') {
                    finalMessage = finalMessage.message;
                }

                span.innerHTML = '<span>' + finalMessage + '</span>';
                span.style.display="block";
                fullScreenLoader.stopLoader();
            },
            async pay() {
                const payButton = $('#brippo-failsafe-paymentelement-pay');
                payButton.prop('disabled', true);
                this.resetPaymentErrors();
                fullScreenLoader.startLoader();

                const onPlaceOrderError = (error) => {
                    this.showPaymentError(error);
                    fullScreenLoader.stopLoader();
                    payButton.prop('disabled', false);
                }

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
                    const {error: paymentMethodError, paymentMethod: paymentMethodResponse} = await this.stripe.createPaymentMethod({
                        elements
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
                const {error: placeOrderError, clientSecret, orderId, orderIncrementId, paymentIntentId} = await this.placeOrderAndPaymentIntent(paymentMethod);
                if (placeOrderError) {
                    onPlaceOrderError(placeOrderError);
                    this.log(placeOrderError, {});
                    return;
                }

                /*
                    * CONFIRM PAYMENT INTENT
                    */
                console.log('Brippo: Confirming payment...');
                try {
                    const responseUrl = url.build('brippo_payments/paymentelement/response') +
                        '?orderId=' + orderId;
                    const {error: confirmError} = await this.stripe.confirmPayment({
                        clientSecret,
                        confirmParams: {
                            payment_method: paymentMethod.id,
                            return_url: responseUrl,
                        }
                    });
                    if (confirmError) {
                        onPlaceOrderError(confirmError);
                        this.cancelOrder(
                            orderId,
                            orderIncrementId,
                            'Payment declined: ' + confirmError.message??JSON.stringify(confirmError)
                        );
                    }
                } catch (ex) {
                    onPlaceOrderError(ex.message);
                    this.cancelOrder(
                        orderId,
                        orderIncrementId,
                        ex.message
                    );
                    this.log(ex.message, ex);
                }
            },
            placeOrderAndPaymentIntent: async function (paymentMethod) {
                let error, clientSecret, orderId, orderIncrementId, paymentIntentId;
                try {
                    await $.ajax({
                        url: url.build('brippo_payments/paymentelement/placeorder'),
                        data: {
                            paymentMethod: paymentMethod,
                            isRecovery: true
                        },
                        type: 'POST',
                        dataType: 'json',
                        showLoader: true
                    }).fail((jqXHR) => {
                        error = this.handleAjaxFail(jqXHR);
                    }).done((response) => {
                        if (response.valid === 0) {
                            error = response.message;
                        } else {
                            clientSecret = response['client_secret'];
                            orderId = response['order_id'];
                            paymentIntentId = response['payment_intent_id'];
                            orderIncrementId = response['order_increment_id'];
                        }
                    });
                } catch (ex) {
                    console.log(ex.message);
                }

                return {error, clientSecret, orderId, orderIncrementId, paymentIntentId}
            },
            resetPaymentErrors: function () {
                let span = document.getElementById('brippo-failsafe-paymentelement-errors');
                if (span) {
                    span.style.display = "none";
                }
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
            handleAjaxFail: function (jqXHR) {
                let error = 'There has been an error processing your request';
                if (jqXHR && typeof jqXHR.error() !== "undefined") {
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
            }
        }
    }
);
