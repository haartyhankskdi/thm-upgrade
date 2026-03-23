/**
 * Copyright © 2020 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'Magento_Payment/js/view/payment/cc-form',
    'mage/storage',
    'mage/url',
    'Magento_Ui/js/model/messageList',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/action/place-order',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/action/set-payment-information-extended',
    'Magento_CheckoutAgreements/js/view/agreement-validation',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/action/set-payment-information'
], function (
    $,
    Component,
    storage,
    url,
    messageList,
    customer,
    placeOrderAction,
    $t,
    fullScreenLoader,
    modal,
    setPaymentInformationExtended,
    agreementValidation,
    additionalValidators,
    urlBuilder,
    quote,
    customerData,
    setPaymentInformation
) {
    'use strict';

    return Component.extend({
        placeOrderHandler: null,
        validateHandler: null,
        modal: null,
        defaults: {
            template: 'Ebizmarts_SagePaySuite/payment/multishipping/pi-form',
            creditCardType: '',
            creditCardExpYear: '',
            creditCardExpMonth: '',
            creditCardLast4: '',
            merchantSessionKey: '',
            cardIdentifier: '',
            dropInInstance: null
        },

        /**
         * {Function}
         */
        fail: function () {
            fullScreenLoader.stopLoader();

            return this;
        },

        /**
         * {Function}
         */
        done: function () {
            fullScreenLoader.stopLoader();
            $('#multishipping-billing-form').submit();

            return this;
        },
        setPlaceOrderHandler: function (handler) {
            this.placeOrderHandler = handler;
        },
        setValidateHandler: function (handler) {
            this.validateHandler = handler;
        },
        getCode: function () {
            return 'sagepaysuitepi';
        },
        getRadioButtonId: function () {
            return 'p_method_' + this.getCode();
        },
        dropInEnabled: function () {
            return window.checkoutConfig.payment.ebizmarts_sagepaysuitepi.dropin == 1;
        },
        threeDNewWindowEnabled: function () {
            return window.checkoutConfig.payment.ebizmarts_sagepaysuitepi.newWindow == 1;
        },
        isActive: function () {
            return true;
        },
        sagepaySetForm: function () {
            var self = this;

            self.addShippingUpdateEvent();
            self.loadDropInForm();
            self.addBillingUpdateEvents();
        },
        watchRadioButton: function () {
            var self = this;

            $("#" + this.getRadioButtonId()).on('click', function () {
                self.sagepaySetForm();
            });
        },
        addShippingUpdateEvent: function () {
            var self = this;

            $(".button.action.continue.primary").on('click', function () {
                self.resetPaymentErrors();
                self.loadDropInForm();
            });
        },
        addBillingUpdateEvents: function () {
            var self = this;

            $("#billing-address-same-as-shipping-sagepaysuitepi").on('change', function () {
                if ($("#billing-address-same-as-shipping-sagepaysuitepi").is(':checked')) {
                    self.resetPaymentErrors();
                    self.loadDropInForm();
                }
            });

            $(".action.action-update").on('click', function () {
                self.resetPaymentErrors();
                self.loadDropInForm();
            });
        },
        selectPaymentMethod: function () {
            return this._super();
        },
        getRemoteJsName: function () {
            var self = this;
            var jsName = 'sagepayjs_';
            if (self.dropInEnabled()) {
                jsName = jsName + 'dropin_';
            }
            return jsName;
        },
        getConfiguredMode: function () {
            return window.checkoutConfig.payment.ebizmarts_sagepaysuitepi.mode;
        },
        getPostCartsUrl: function () {
            return urlBuilder.createUrl('/carts/mine/billing-address', {});
        },
        createMerchantSessionKey: function () {
            var self = this;
            storage.get(urlBuilder.createUrl('/elavon/pi-msk', {})).done(
                function (response) {
                    if (response.success) {
                        self.sagepayTokeniseCard(response.response);
                    } else {
                        self.showPaymentError(response.error_message);
                    }
                }
            ).fail(
                function (response) {
                    self.showPaymentError("Unable to create Elavon merchant session key.");
                }
            );
        },
        preparePayment: function () {
            var self = this;

            self.destroyInstanceSagePay();

            //validations
            if (!self.validate() || self.getCode() != self.isChecked()) {
                return false;
            }

            fullScreenLoader.startLoader();

            requirejs([self.getRemoteJsName() + self.getConfiguredMode()], function () {

                self.createMerchantSessionKey();

             });

            return false;
        },
        loadDropInForm: function () {
            var self = this;
            $('#pi-form-multishipping').on('click', function() {
                $('#pi-form-multishipping').show();
            });

            if (document.getElementById(this.getRadioButtonId()).checked) {
                self.selectPaymentMethod();
            }

            if (self.dropInEnabled() && quote.billingAddress() != null) {
                self.preparePayment();
            }
        },
        tokenisationAuthenticationFailed: function (tokenisationResult) {
            return tokenisationResult.error.errorCode === 1002;
        },
        tokenise: function () {
            var self = this;

            if (additionalValidators.validate()) {
                    if (self.dropInInstance !== null) {
                        self.dropInInstance.tokenise();
                    }
            } else {
                return false;
            }
        },
        destroyInstanceSagePay: function () {
            var self = this;
            if (!self.dropInEnabled()) {
                return;
            }

            if (self.dropInInstance !== null) {
                self.dropInInstance.destroy();
                self.dropInInstance = null;
            }

            self.isPlaceOrderActionAllowed(true);
        },
        isPlaceOrderActionAllowed: function (allowedParam) {
            if (typeof allowedParam === 'undefined') {
                return quote.billingAddress() != null;
            }
            return allowedParam;
        },
        sagepayTokeniseCard: function (merchant_session_key) {
            var self = this;
            if (merchant_session_key) {
                self.isPlaceOrderActionAllowed(false);
                self.merchantSessionKey = merchant_session_key;

                if (self.dropInInstance !== null) {
                    self.dropInInstance.destroy();
                    self.dropInInstance = null;
                }

                self.dropInInstance = sagepayCheckout({
                    merchantSessionKey: merchant_session_key,
                    onTokenise: function (tokenisationResult) {
                        if (tokenisationResult.success) {
                            self.cardIdentifier = tokenisationResult.cardIdentifier;
                            self.creditCardType = "";
                            self.creditCardExpYear = 0;
                            self.creditCardExpMonth = 0;
                            self.creditCardLast4 = 0;
                            try {
                                self.placeTransaction();
                            } catch (err) {
                                console.log(err);
                                self.showPaymentError("Unable to initialize Elavon payment method, please use another payment method.");
                            }
                        } else {
                            //Check if it is "Authentication failed"
                            if (self.tokenisationAuthenticationFailed(tokenisationResult)) {
                                self.destroyInstanceSagePay();
                                self.resetPaymentErrors();
                            } else {
                                self.showPaymentError('Tokenisation failed', tokenisationResult.error.errorMessage);
                            }
                        }
                    }
                });
                self.dropInInstance.form();
                fullScreenLoader.stopLoader();

                $("#payment-iframe").css("display", "block");
                $("#sp-container").css("display", "block");
                $("#submit_dropin_payment").css("display", "block");
                $("#load-dropin-form-button").css("display", "block");
            }
        },
        getPlaceTransactionUrl: function () {
            var serviceUrl = null;
            serviceUrl = urlBuilder.createUrl('/elavon-ms/pi', {});
            return serviceUrl;
        },
        placeTransaction: function () {
            var self = this;

            var sagePayRequestData = {
                "merchant_session_key": self.merchantSessionKey,
                "card_identifier": self.cardIdentifier,
                "javascript_enabled": 1,
                "accept_headers": '*\/*',
                "language": navigator.language,
                "user_agent": navigator.userAgent,
                "java_enabled": navigator.javaEnabled() ? 1 : 0,
                "color_depth": screen.colorDepth,
                "screen_width": screen.width,
                "screen_height": screen.height,
                "timezone": (new Date()).getTimezoneOffset()
            };

            var payload = {
                "cartId": quote.getQuoteId(),
                "requestData": sagePayRequestData
            };

            var serviceUrl = self.getPlaceTransactionUrl();
            storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).done(
                function (response) {
                    fullScreenLoader.stopLoader();

                    if (response.success) {
                        if (response.status === "Ok") {
                            $('#pi-form-multishipping').hide();
                        }  else {
                            self.showPaymentError("There was a problem processing your card details, please try again.");
                        }
                    } else {
                        self.showPaymentError(response.error_message);
                        self.destroyInstanceSagePay();
                    }
                }
            ).fail(
                function (response) {
                    self.showPaymentError("Failed to retrieve SCA params");
                }
            );
        },
        /**
         * @override
         */
        getData: function () {
            return {
                'method': this.getCode(),
                'additional_data': {
                    'cc_last4': this.creditCardLast4,
                    'merchant_session_key': this.merchantSessionKey,
                    'card_identifier': this.cardIdentifier,
                    'cc_type': this.creditCardType,
                    'cc_exp_year': this.creditCardExpYear,
                    'cc_exp_month': this.creditCardExpMonth
                }
            };
        },
        /**
         * Place order.
         */
        placeOrder: function (data, event) {

            if (event) {
                event.preventDefault();
            }
            var self = this,
                placeOrder,
                emailValidationResult = customer.isLoggedIn(),
                loginFormSelector = 'form[data-role=email-with-possible-login]';
            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
            }
            if (emailValidationResult && this.validate()) {
                this.isPlaceOrderActionAllowed(false);
                placeOrder = placeOrderAction(this.getData(), false);

                $.when(placeOrder).done(
                    function (order_id, response, extra) {
                        window.location.replace(url.build('checkout/onepage/success/'));
                    }
                ).fail(
                    function (response) {
                        self.isPlaceOrderActionAllowed(true);

                        var error_message = "Unable to capture payment. Please refresh the page and try again.";
                        if (response && response.responseJSON && response.responseJSON.message) {
                            error_message = response.responseJSON.message;
                        }
                        self.showPaymentError(error_message);
                    }
                );
                return true;
            }
            return false;
        },
        showPaymentError: function (message) {
            var self = this;

            var span = document.getElementById('sagepaysuitepi-payment-errors');

            span.innerHTML = message;
            span.style.display = "block";

            fullScreenLoader.stopLoader();

            self.loadDropInForm();
        },
        resetPaymentErrors: function () {
            var span = document.getElementById('sagepaysuitepi-payment-errors');

            if (null !== span) {
                span.style.display = "none";
            }
        },
    });
});
