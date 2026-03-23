/**
 * Consent popup logic
 */
define([
    'jquery',
    'mage/url',
    'mage/translate',
    'Magento_Ui/js/modal/modal-component',
    'text!Amasty_Gdpr/template/modal/modal-popup.html'
], function ($, urlBuilder, $t, modal, popupTpl) {
    'use strict';

    return modal.extend({
        defaults: {
            textUrl: '',
            acceptUrl: '',
            popupDataUrl: '',
            htmlContent: '',
            notificationText:'',
            versionChanged: false,
            consentPolicy: {},
            options: {
                autoOpen: false,
                type: 'popup',
                title: $t('Privacy Policy'),
                closeText: $t('Close') + ' ' + $t('Privacy Policy'),
                modalClass: 'amgdpr-modal-container',
                popupTpl: popupTpl,
                buttons: [ {
                    text: $t('I have read and accept'),
                    class: 'action action-primary',
                    actions: [ {
                        'targetName': '${ $.name }',
                        'actionName': 'acceptPolicy'
                    } ]
                } ]
            }
        },

        initialize: function () {
            this._super()._addFormKeyIfNotSet().showPopupWithConsentPolicy();

            return this;
        },

        initObservable: function () {
            this._super()
                .observe([
                    'htmlContent',
                    'versionChanged'
                ]);

            return this;
        },

        showPopupWithConsentPolicy: function () {
            $.ajax({
                url: this.popupDataUrl,
                method: 'GET',
                success: function (data) {
                    if (data.show) {
                        this.showPopup(data);
                    }
                }.bind(this)
            });
        },

        showPopup: function (consentPolicy) {
            this.consentPolicy = consentPolicy;
            this.consentPolicy['form_key'] = window.FORM_KEY;
            this.versionChanged(consentPolicy.versionChanged);

            $.ajax({
                url: this.textUrl,
                method: 'GET',
                success: function (data) {
                    this.htmlContent(data.content);
                    this.openModal();
                }.bind(this)
            });
        },

        acceptPolicy: function () {
            if (!this.acceptUrl || !this.consentPolicy.policyVersion) {
                return;
            }

            $('body').trigger('processStart');
            $.ajax({
                url: this.acceptUrl,
                method: 'POST',
                data: this.consentPolicy,
                complete: function () {
                    this.closeModal();
                    $('body').trigger('processStop');
                }.bind(this)
            });
        },

        _addFormKeyIfNotSet: function () {
            if (!window.FORM_KEY) {
                window.FORM_KEY = $.mage.cookies.get('form_key');
            }

            return this;
        }
    });
});
