define(
    [
        'jquery',
        'mage/validation'
    ],
    function ($) {
        'use strict';
        var checkoutConfig = window.checkoutConfig,
            gdprConfig = checkoutConfig ? checkoutConfig.amastyGdprConsent : {};

        return {
            consentInputPath: 'div.amasty-gdpr-consent:visible input',

            /**
             * Validate checkout agreements
             *
             * @param {boolean} hideError
             * @returns {boolean}
             */
            validate: function (hideError) {
                var isValid = true,
                    consentInput;

                consentInput = $(this.consentInputPath);

                if (!consentInput.length) {
                    return true;
                }

                consentInput.each(function (i, element) {
                    var isCheckboxValid = $.validator.validateSingleElement(element, {
                        errorElement: 'div',
                        errorClass: 'mage-error',
                        meta: 'validate',
                        hideError: Boolean(hideError),
                        errorPlacement: function (error, element) {
                            element.siblings('label').last().after(error);
                        }
                    });

                    if (!isCheckboxValid) {
                        isValid = false;
                    }
                });

                return isValid;
            }
        };
    }
);
