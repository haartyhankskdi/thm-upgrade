'use strict';

const onBrippoPaymentsInitializationCompleteEvent = new CustomEvent('onBrippoPaymentsInitializationComplete', {});

const BrippoPayments = {
    stripe: null,
    wasInitialized: false,
    config: null,
    timesPaymentConfirmationFailed: 0,
    async initialize() {
        if (!this.wasInitialized) {
            this.wasInitialized = true;
            this.config = await this.getConfiguration();
            if (this.config) {
                this.stripe = Stripe(
                    this.config.publishableKey,
                    {
                        apiVersion: this.config.apiVersion
                    }
                );
                document.dispatchEvent(onBrippoPaymentsInitializationCompleteEvent);
            }
        }
    },
    async getConfiguration() {
        let config;
        const url = window.brippoConfigurationUrl || '/brippo_payments/payments/configuration';
        await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
        })
            .then(response => {
                if (!response.ok) {
                    console.log(response);
                    throw new Error('There was a problem while fetching Brippo configuration.');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.valid === 1) {
                    config = data;
                } else {
                    console.log('There was a problem while fetching Brippo configuration:', data?.message);
                }
            })
            .catch(error => {
                console.error('There was a problem while fetching Brippo configuration:', error);
            });

        return config;
    },
    generateThrobber() {
        const throbberElement = document.createElement('div');
        throbberElement.innerHTML = '<div class="throbber"></div>';
        throbberElement.id = 'brippoPaymentsThrobber';
        throbberElement.className = 'brippoThrobberVeil';
        document.body.appendChild(throbberElement);
    },
    showVeilThrobber() {
        const throbber = document.getElementById('brippoPaymentsThrobber');
        if (throbber) {
            throbber.style.display = 'block'; // Show the element
        }
    },
    hideVeilThrobber() {
        const throbber = document.getElementById('brippoPaymentsThrobber');
        if (throbber) {
            throbber.style.display = 'none'; // Hide the element
        }
    },
    log(step, errorMessage) {
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.general.controllers.log);
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                step: step,
                errorMessage: errorMessage
            })
        }).then();
    },
    waitForElementToExists(selector, callback, retry = 0) {
        setTimeout(() => {
            if (document.querySelector(selector)) {
                // Element exists, call the callback
                callback();
            } else {
                // Element not found, retry
                retry++;
                console.log('Brippo Payments: Can not find ' + selector + '. Retry ' + retry + '...');
                if (retry < 100) {
                    this.waitForElementToExists(selector, callback, retry);
                }
            }
        }, retry === 0 ? 1 : 500);
    },
    waitForBody(callback) {
        if (document.body) {
            callback();
        } else {
            requestAnimationFrame(() => this.waitForBody(callback));
        }
    },
    isMobileScreen() {
        return window.innerWidth <= 700;
    },
    isRecoverCheckout() {
        return window.location.href.indexOf('/payments/recover') !== -1;
    },
    isDeclineCodeAllowedForRecovery(confirmationError) {
        if (this.config?.general?.allowFailedPayments
            && this.config.general.allowFailedPayments === '1'
            && confirmationError?.code
            && this.config?.general?.errorCodesAllowedForRecovery
            && (this.config.general.errorCodesAllowedForRecovery.includes(confirmationError.code)
                || this.config.general.errorCodesAllowedForRecovery.includes(confirmationError.decline_code))
        ) {
            if (this.config.general.allowFailedPaymentsSecondAttempt !== '1'
                || this.timesPaymentConfirmationFailed > 1) {
                return true;
            }
        }
        return false;
    },
    async getPaymentStatus(paymentIntentId) {
        let status;
        const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.general.controllers.paymentStatus);
        await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                paymentIntentId: paymentIntentId
            })
        })
            .then(response => {
                if (!response.ok) {
                    console.log(response);
                    throw new Error('There was a problem while fetching payment status.');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.valid === 1) {
                    status = data.status;
                } else {
                    console.log('There was a problem while fetching payment status:', data?.message);
                }
            })
            .catch(error => {
                console.error('There was a problem while fetching payment status:', error);
            });

        return status;
    },
    getCookie(name) {
        const match = document.cookie.match(
            new RegExp('(?:^|; )' +
                name.replace(/([$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') +
                '=([^;]*)'
            )
        );
        return match ? decodeURIComponent(match[1]) : null;
    },
    normalizeFormKeyInUrl(url) {
        const realKey = this.getCookie('form_key');
        if (!realKey) {
            console.warn('No form_key cookie found – URL left unchanged');
            return url;
        }

        // If there’s already a /form_key/{something}/ segment, replace it.
        if (/\/form_key\/[^\/]+/.test(url)) {
            return url.replace(
                /\/form_key\/[^\/]+/,
                `/form_key/${encodeURIComponent(realKey)}`
            );
        }

        // Otherwise append it at the end (ensuring a trailing slash)
        return url.replace(/\/?$/, `/form_key/${encodeURIComponent(realKey)}/`);
    },
    logOrderEvent(orderId, eventMessage) {
        try {
            const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.general.controllers.logOrderEvent);
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    orderId,
                    eventMessage
                })
            })
                .then()
                .catch((e) => {
                    console.error('Unable to log Brippo order event', e);
                });
        } catch (e) {
            console.error('Unable to log Brippo order event', e);
        }
    },
    reportAnalytic(environment, eventType, message) {
        try {
            const url = BrippoPayments.normalizeFormKeyInUrl(BrippoPayments.config.general.controllers.analytic);
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    environment,
                    message,
                    eventType
                })
            })
                .then()
                .catch((e) => {
                    console.error('Unable to report Brippo analytic', e);
                });
        } catch (e) {
            console.error('Unable to report Brippo analytic', e);
        }
    },
    prettifyErrorMessage(message) {
        if (message.includes('The string did not match the expected pattern')
            || message.includes('in JSON at position 0')) {
            return 'Invalid form key. Please refresh the page.'
        }
        return message;
    }
}

BrippoPayments.waitForBody(() => {
    BrippoPayments.generateThrobber();
});
