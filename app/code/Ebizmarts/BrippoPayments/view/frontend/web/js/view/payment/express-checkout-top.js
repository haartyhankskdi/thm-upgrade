define(
    [
        'jquery',
        'uiComponent',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'ko',
        'brippo_payment_request_button',
        'mage/translate',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_CheckoutAgreements/js/model/agreement-validator',
        'Magento_Checkout/js/action/select-payment-method'
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
        selectPaymentMethodAction
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                isRendered: false,
                wasMovedToPlacementPosition: false
            },
            initObservable: function () {
                this._super()
                    .observe([
                        'canMakePayment'
                    ]);

                this.shouldDisplayAtCheckoutTop = ko.computed(() => {
                    return this.isNotZeroAmountOrder() &&
                        window.checkoutConfig.payment.stripeconnect_express !== undefined &&
                        window.checkoutConfig.payment.stripeconnect_express.enabledInCheckout &&
                        this.shouldDisplayOnTop();
                }, this);

                if (!this.shouldDisplayOnTop()) {
                    return this;
                }

                brippoExpress.onCanMakePaymentHandlers.push(() => {
                    $('.brippo-on-top-title').html(this.methodTopTitle());
                    this.moveToPlacementPosition();
                    this.canMakePayment(true);
                });

                quote.totals.subscribe((totals) => {
                    const cartGrandTotal = Number(parseFloat(quote.totals()['grand_total']) * 100);
                    if (brippoExpress.lastTotalRequested &&
                        'checkout' in brippoExpress.lastTotalRequested &&
                        brippoExpress.lastTotalRequested['checkout'] === cartGrandTotal) {
                        console.log('Wont update request as cart total didn\'t change.');
                        return;
                    }
                    this.onTotalsCalculated();
                });

                quote.paymentMethod.subscribe(function (method) {
                    if (method != null) {
                        //toDo check this selector
                        $(".payment-method.scExpress.mobile").removeClass("_active");
                    }
                }, null, 'change');

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

                return this;
            },
            isNotZeroAmountOrder: function () {
                return parseFloat(quote.totals()['grand_total']) > 0;
            },
            onTotalsCalculated: function () {
                setTimeout(() => {
                    this.initRequest();
                }, 100);
            },
            moveToPlacementPosition() {
                if (!this.wasMovedToPlacementPosition) {
                    const layoutPlacementSelector = window.checkoutConfig.payment.stripeconnect_express.layoutPlacementSelector;
                    if (layoutPlacementSelector && layoutPlacementSelector !== '' && document.querySelector(layoutPlacementSelector)) {
                        const targetElement = document.querySelector(layoutPlacementSelector);
                        const brippoButtonElement = document.querySelector('.brippo-checkout-ontop');
                        targetElement.prepend(brippoButtonElement);
                    }
                    this.wasMovedToPlacementPosition = true;
                }
            },
            onRenderedHandler: function () {
                this.isRendered = true;
                if (this.shouldDisplayOnTop()) {
                    this.initRequest();
                }
            },
            initRequest: function () {
                if (window.checkoutConfig.payment.stripeconnect_express === undefined ||
                    !window.checkoutConfig.payment.stripeconnect_express.enabledInCheckout ||
                    !this.isRendered ||
                    !this.isNotZeroAmountOrder()) {
                    return;
                }

                brippoExpress.initPaymentRequest(
                    window.checkoutConfig.payment.stripeconnect_express,
                    {
                        source: 'checkout',
                        elementId: 'brippo-payments-express-button'
                    },
                    (prButton) => { this.onButtonReadyHandler(prButton); }
                );
            },
            methodTopTitle: function () {
                const walletName = brippoExpress.getWalletName();
                if (walletName && walletName !== '') {
                    return $t('Checkout with') + ' ' + walletName;
                }
                return '';
            },
            onButtonReadyHandler: function (prButton) {
                if (!prButton) {
                    return;
                }
                prButton.on('click', (ev) => {
                    this.makeActive();
                    if (!this.validate()) {
                        ev.preventDefault();
                    }
                });
            },
            makeActive: function () {
                if (window.checkoutConfig.payment.stripeconnect_express === undefined ||
                    !window.checkoutConfig.payment.stripeconnect_express.enabledInCheckout) {
                    return;
                }

                if (!this.isOnlyAgreementsValidationMode()) {
                    try {

                        selectPaymentMethodAction(null);
                    } catch (e) {
                        console.log(e);
                    }

                    $(".payment-method.scExpress.mobile").addClass("_active");
                }
            },
            isOnlyAgreementsValidationMode() {
                return window.checkoutConfig.payment.stripeconnect_express
                    && window.checkoutConfig.payment.stripeconnect_express.validationMode === 'only-agreements';
            },
            validate: function (region) {
                if (agreementValidator.validate() && (this.isOnlyAgreementsValidationMode() || additionalValidators.validate())) {
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
            shouldDisplayOnTop: function () {
                return window.checkoutConfig.payment.stripeconnect_express !== undefined &&
                    window.checkoutConfig.payment.stripeconnect_express.checkoutLocation === 'on_top';
            }
        });
    }
);
