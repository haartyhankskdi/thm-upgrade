
window.brippoRecoverCheckout = {
    paymentIntegrationsReady: 0,
    getPaymentIntegrationsSeparator() {
        return document.querySelector('.brippoRecoverOrder .brippoCheckout .separator');
    },
    getCheckoutLoadingThrobber() {
        return document.querySelector('.brippoRecoverOrder .loadingThrobber');
    },
    onBrippoOnIntegrationReady(event) {
        this.paymentIntegrationsReady++;
        if (this.paymentIntegrationsReady > 0) {
            this.getCheckoutLoadingThrobber().style.display = 'none';
        }
        if (this.paymentIntegrationsReady > 1) {
            this.getPaymentIntegrationsSeparator().style.display = 'flex';
        }
    }
}

document.addEventListener('brippoOnIntegrationReady', window.brippoRecoverCheckout.onBrippoOnIntegrationReady.bind(window.brippoRecoverCheckout));
