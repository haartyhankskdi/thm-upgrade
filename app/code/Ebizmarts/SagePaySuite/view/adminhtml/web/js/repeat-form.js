/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

/*jshint jquery:true*/

define([
    "jquery",
    'mage/url',
    "sagepayerror",
    'mage/translate'
], function ($, url, sagepayerror) {
    "use strict";

    /**
     * Disable card server validation in admin
     */
    if (typeof variable !== 'undefined') {
    order.addExcludedPaymentMethod('sagepaysuiterepeat');
    }

    $.widget('mage.sagepaysuiteRepeatForm', {
        options: {
            code: "sagepaysuiterepeat"
        },

        prepare: function (event, method) {
            if (method === this.options.code) {
                this.preparePayment();
            }
        },
        preparePayment: function () {
            $('#edit_form').off('submitOrder').on('submitOrder', this.submitAdminOrder.bind(this));
            $('#edit_form').off('changePaymentData').on('changePaymentData', this.changePaymentData.bind(this));
        },
        changePaymentData: function () {
            //console.log("changePaymentData");
        },
        fieldObserver: function () {
            //console.log("fieldObserver");
        },
        submitAdminOrder: function () {

            var self = this;
            sagepayerror.resetPaymentErrors(self.getCode());

            var serviceUrl = this.options.url.request;

            var formData = jQuery("#edit_form").serialize();

            var payload = {
                vpstxid: $('#' + self.getCode() + '_vpstxid').val(),
                form_key: window.FORM_KEY
            };

            var query = $.param(payload);
            formData += "&";
            formData += query;

            jQuery.ajax({
                url: serviceUrl,
                data: formData,
                type: 'POST'
            }).done(function (response) {
                if (response.success == true) {
                    //redirect to success
                    window.location.href = response.response.data.redirect;
                } else {
                    $('#sagepaysuiterepeat_vpstxid').val('');
                    sagepayerror.showPaymentError(self.getCode(), $.mage.__(response.error_message ? response.error_message : "Invalid Opayo response."));
                }
            });
        },
        getCode: function () {
            return this.options.code;
        },
        _create: function () {
            $('#edit_form').on('changePaymentMethod', this.prepare.bind(this));
            $('#edit_form').on('changePaymentData', this.changePaymentData.bind(this));
            $('#edit_form').trigger(
                'changePaymentMethod',
                [
                    $('#edit_form').find(':radio[name="payment[method]"]:checked').val()
                ]
            );
        }
    });

    return $.mage.sagepaysuiteRepeatForm;
});
