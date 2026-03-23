define([
	'jquery',
	'Magento_Ui/js/modal/alert',
	'mage/translate'
], function($, alert) {
	"use strict";
	return {
		showPaymentError: function(code, message) {
			var info_message = document.getElementById(code + '-info-message');
			if (info_message) {
				info_message.style.display = "none";
			}
			var span = document.getElementById(code + '-payment-errors');
			$('#edit_form').trigger('processStop');
			$('body').trigger('processStop');
			alert({
				title: $.mage.__('Payment not completed'),
				content: $.mage.__("Your card data was sent, but it seems we had a problem processing your payment. Check the error message in our credit card form to see what happened."),
				clickableOverlay: false,
				modalClass: 'alert-payment-sagepaysuite-error',
				buttons: [{
					text: $.mage.__('Ok'),
					class: 'action primary accept error',
					click: function() {
						this.closeModal(true);
					}
				}]
			});
			$(".alert-payment-sagepaysuite-error [data-role='closeBtn']").remove();
			span.style.display = 'block';
			span.innerHTML = message;
		},
		resetPaymentErrors: function(code) {
			var span = document.getElementById(code + '-payment-errors');
			if (span) {
				span.style.display = "none";
			}
		}
	};
});
