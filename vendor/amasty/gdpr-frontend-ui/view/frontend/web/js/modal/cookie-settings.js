define([
    'jquery',
    'uiLayout',
    'Magento_Ui/js/modal/modal-component',
    'text!Amasty_GdprFrontendUi/template/components/modal/cookie-settings/modal-popup.html',
    'Amasty_GdprFrontendUi/js/model/cookie-data-provider',
    'Amasty_GdprFrontendUi/js/action/save',
    'mage/translate'
], function (
    $,
    layout,
    Modal,
    popupTpl,
    cookieDataProvider,
    actionSave,
    $t
) {
    'use strict';

    return Modal.extend({
        defaults: {
            template: 'Amasty_GdprFrontendUi/components/modal/cookie-settings',
            name: 'gdpr-cookie-settings-modal',
            showClass: '-show',
            options: {
                popupTpl: popupTpl,
                modalClass: 'amgdprcookie-groups-modal amgdprcookie-cookie-settings-modal',
                title: $t('Please select and accept your Cookies Group')
            },
            selectors: {
                settingsFooterLink: '[data-amcookie-js="footer-link"]',
                settingsGdprLink: '[data-amgdpr-js="cookie-link"]'
            },
            settings: {
                backgroundColor: null,
                groupTitleTextColor: null,
                groupDescriptionTextColor: null,
                groupLinksColor: null,
                doneButtonText: $.mage.__('Done'),
                doneButtonColor: null,
                doneButtonColorHover: null,
                doneButtonTextColor: null,
                doneButtonTextColorHover: null,
                toggleActiveColor: null,
                toggleInActiveColor: null
            },
            items: [],
        },

        initialize: function () {
            this._super();
            this.passSettingsToModalTemplate();
            this.bindWithFooterLink();
            this.updateItems();
        },

        initObservable: function () {
            return this._super()
                .observe({
                    items: []
                });
        },

        openModal: function () {
            this.updateItems();
            this._super();
        },

        updateItems: function () {
            cookieDataProvider.getCookieData().done((cookieData) => {
                this.items(cookieData.groupData);
            });
        },

        saveCookie: function (element, parent) {
            actionSave(element).done(() => {
                parent?.closeModal?.();
                parent?.closeCookieBar?.();
                this.closeModal();
            });
        },

        passSettingsToModalTemplate: function () {
            this.options.settings = this.settings;
        },

        bindWithFooterLink: function () {
            const links = $(this.selectors.settingsFooterLink + ',' + this.selectors.settingsGdprLink);
            $(links).addClass(this.showClass).on('click', (event) => {
                event.preventDefault();
                this.openModal();
            });
        }
    });
});
