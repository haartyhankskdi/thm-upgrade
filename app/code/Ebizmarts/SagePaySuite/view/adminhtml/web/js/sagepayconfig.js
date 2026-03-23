define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'Magento_Ui/js/modal/confirm',
        'mage/translate'
    ],
    function ($, alert, confirmation) {
        "use strict";

        $.widget('mage.configsagepaysuite', {
            "options": {
                "registerUrl": "",
                "needToRegister": "",
                "scope": "",
                "scopeId": "",
                "prefix": "",
                "configurationGuideUrl": ""
            },
            _init: function () {
                var self = this;
                var licenceId = '#'+this.options.prefix+'_sagepaysuite_global_license';
                var vendorId = '#'+this.options.prefix+'_sagepaysuite_global_vendorname';
                var nameId = '#'+this.options.prefix+'_sagepaysuite_global_name';
                var emailId = '#'+this.options.prefix+'_sagepaysuite_global_email';
                var phoneCodeId = '#'+this.options.prefix+'_sagepaysuite_global_phone_code';
                var phoneNumberId = '#'+this.options.prefix+'_sagepaysuite_global_phone_number';
                var registerRowId = '#row_'+this.options.prefix+'_sagepaysuite_global_register_licence_key';
                var registerId = '#'+this.options.prefix+'_sagepaysuite_global_register_licence_key';
                window.sagePaySuitePrefix = this.options.prefix;
                $('#'+this.options.prefix+'_sagepaysuite_global_repeat_transaction').change(function () {
                    var repeatTransactionValue = $('#'+window.sagePaySuitePrefix+'_sagepaysuite_global_repeat_transaction').val();
                    var tokenSettingValue = $('#'+window.sagePaySuitePrefix+'_sagepaysuite_global_token').val();
                    var repeatPaymentSettingValue = $('#'+window.sagePaySuitePrefix+'_sagepaysuite_sagepaysuiterepeat_active').val();
                    self._validateRepeatTransactionConfig(repeatTransactionValue, tokenSettingValue, repeatPaymentSettingValue);
                });
                $('#'+this.options.prefix+'_sagepaysuite_global_token').change(function () {
                    var repeatTransactionValue = $('#'+window.sagePaySuitePrefix+'_sagepaysuite_global_repeat_transaction').val();
                    var tokenSettingValue = $('#'+window.sagePaySuitePrefix+'_sagepaysuite_global_token').val();
                    self._validateUseTokenConfig(repeatTransactionValue, tokenSettingValue);
                });
                $('#'+this.options.prefix+'_sagepaysuite_sagepaysuiterepeat_active').change(function () {
                    var repeatTransactionValue = $('#'+window.sagePaySuitePrefix+'_sagepaysuite_global_repeat_transaction').val();
                    var repeatPaymentSettingValue = $('#'+window.sagePaySuitePrefix+'_sagepaysuite_sagepaysuiterepeat_active').val();
                    self._validateRepeatPaymentConfig(repeatTransactionValue, repeatPaymentSettingValue);
                });
                $('#'+this.options.prefix+'_sagepaysuite_sagepaysuitepi_use_dropin').change(function () {
                    var piMethod = '_sagepaysuite_sagepaysuitepi_use_dropin';
                    self._dropinChanged(piMethod);
                });
                $('#'+this.options.prefix+'_sagepaysuite_sagepaysuitepimoto_use_dropin').change(function () {
                    var piMethod = '_sagepaysuite_sagepaysuitepimoto_use_dropin';
                    self._dropinChanged(piMethod);
                });
                if (!this.options.needToRegister) {
                    $(registerRowId).hide();
                } else {
                    $(registerId).click(function () {
                        var license = $(licenceId).val();
                        var vendor = $(vendorId).val();
                        var name = $(nameId).val();
                        var email = $(emailId).val();
                        var phone_code = $(phoneCodeId).val();
                        var phone_number = $(phoneNumberId).val();
                        self._registerLicense(license, vendor, name, email, phone_code, phone_number);
                    });
                }
                self._setupInvalidCredentialsMessageLink(this.options.configurationGuideUrl);
            },
            _dropinChanged: function (piMethod)
            {
                var tag = '#'+this.options.prefix+piMethod;
                var val = $(tag).find(':selected').val();
                if (val==0) {
                    confirmation({
                        title: $.mage.__('WARNING Disable dropin'),
                        content: $.mage.__('We don\'t recommend not using dropin, are you sure?'),
                        actions: {
                            cancel: function() {
                                $(tag).empty();
                                $(tag).append($('<option>', {
                                    value: "0",
                                    text: 'No'
                                }));
                                $(tag).append($('<option>', {
                                    value: "1",
                                    text: 'Yes',
                                    selected: "selected"
                                }));
                            }
                        }
                    });

                }
            },
            _registerLicense: function (license, vendor, name, email, phone_code, phone_number) {
                var registerLicenseUrl = this.options.registerUrl;
                var registerRowId = '#row_'+this.options.prefix+'_sagepaysuite_global_register_licence_key';
                $.ajax({
                    url: registerLicenseUrl,
                    data: {'form_key': window.FORM_KEY,
                        'license': license,
                        'vendor': vendor,
                        'name': name,
                        'email': email,
                        'phone_code': phone_code,
                        'phone_number': phone_number,
                        'scope': this.options.scope,
                        'scopeId': this.options.scopeId
                    },
                    type: 'GET',
                    dataType: 'json',
                    showLoader: true
                }).done(function (data) {
                    if (data.valid == 0) {
                        alert({content: 'Error: '+data.message});
                    } else if (data.valid == 1) {
                        alert({content: 'Thanks for register your copy'});
                        $(registerRowId).hide();
                    }
                });
            },
            _validateRepeatTransactionConfig: function (repeatTransaction, tokenSetting, repeatPaymentSetting) {
                let self = this;
                if (repeatTransaction === "0") {
                    window.sagePaySuiteTitle = $.mage.__('Disable Repeat Transaction setting');
                    if (tokenSetting === "1" && repeatPaymentSetting === "1") {
                        window.sagePaySuiteMessage = $.mage.__('If you disable this setting the following settings will be disable SAVE CREDIT CARD TOKENS and REPEAT PAYMENT');
                        self._showConfirmationPopup(0, 0, 0);
                    } else if (tokenSetting === "1") {
                        window.sagePaySuiteMessage = $.mage.__('If you disable this setting the following settings will be disable SAVE CREDIT CARD TOKENS');
                        self._showConfirmationPopup(0, 0, 2);
                    } else if (repeatPaymentSetting === "1") {
                        window.sagePaySuiteMessage = $.mage.__('If you disable this setting the following payment will be disable REPEAT PAYMENT');
                        self._showConfirmationPopup(0, 2, 0);
                    }
                }
            },
            _validateUseTokenConfig: function (repeatTransaction, tokenSetting) {
                let self = this;
                if (tokenSetting === "1") {
                    if (repeatTransaction === "0") {
                        window.sagePaySuiteTitle = $.mage.__('Enabling SAVE CREDIT CARD TOKENS setting');
                        window.sagePaySuiteMessage = $.mage.__('If you enable this setting the following settings will be enable ALLOW REPEAT TRANSACTION');
                        self._showConfirmationPopup(1, 1, 2);
                    }
                }
            },
            _validateRepeatPaymentConfig: function (repeatTransaction, repeatPayment) {
                let self = this;
                if (repeatPayment === "1") {
                    if (repeatTransaction === "0") {
                        window.sagePaySuiteTitle = $.mage.__('Enabling REPEAT PAYMENT');
                        window.sagePaySuiteMessage = $.mage.__('If you enable this setting the following settings will be enable ALLOW REPEAT TRANSACTION');
                        self._showConfirmationPopup(1, 2, 1);
                    }
                }
            },
            _showConfirmationPopup: function (globalrepeat, token, payment) {
                // 0 - disable
                // 1 - enable
                // 2 - don't touch
                let self = this;
                window.sagePaySuiteRepeatPaymentPath = '#'+window.sagePaySuitePrefix+'_sagepaysuite_sagepaysuiterepeat_active';
                window.sagePaySuiteUseTokenPath = '#'+window.sagePaySuitePrefix+'_sagepaysuite_global_token';
                window.sagePaySuitePath = '#'+window.sagePaySuitePrefix+'_sagepaysuite_global_repeat_transaction';
                confirmation({
                    title: window.sagePaySuiteTitle,
                    content: window.sagePaySuiteMessage,
                    actions: {
                        confirm: function() {
                            self._setSelect(globalrepeat, window.sagePaySuitePath);
                            self._setSelect(token, window.sagePaySuiteUseTokenPath);
                            self._setSelect(payment, window.sagePaySuiteRepeatPaymentPath);
                        },
                        cancel: function() {
                            if (payment !== 2) {
                                payment = payment === 1 ? 0 : 1;
                                self._setSelect(payment, window.sagePaySuiteRepeatPaymentPath);
                            }
                            if (token !== 2) {
                                token = token === 1 ? 0 : 1;
                                self._setSelect(token, window.sagePaySuiteUseTokenPath);
                            }
                            if (globalrepeat !== 2) {
                                globalrepeat = globalrepeat === 1 ? 0 : 1;
                                self._setSelect(globalrepeat,  window.sagePaySuitePath);
                            }
                        }
                    }
                });
            },
            _setSelect: function (value, tag) {
                if (value === 0) {
                    $(tag).empty();
                    $(tag).append($('<option>', {
                        value: "0",
                        text: 'No',
                        selected: "selected"
                    }));
                    $(tag).append($('<option>', {
                        value: "1",
                        text: 'Yes'
                    }));
                } else if (value === 1) {
                    $(tag).empty();
                    $(tag).append($('<option>', {
                        value: "0",
                        text: 'No'
                    }));
                    $(tag).append($('<option>', {
                        value: "1",
                        text: 'Yes',
                        selected: "selected"
                    }));
                }
            },
            _setupInvalidCredentialsMessageLink: function (url) {
                var guide_message = $("#opayo-configurationguideurl");
                if (guide_message){
                    guide_message.attr('href', url);
                    guide_message.attr('target', '_blank');
                }
            }
        });
        return $.mage.configsagepaysuite;
    }
);
