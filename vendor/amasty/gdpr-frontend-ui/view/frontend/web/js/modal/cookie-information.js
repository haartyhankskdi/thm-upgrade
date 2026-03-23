define([
    'jquery',
    'uiLayout',
    'Magento_Ui/js/modal/modal-component',
    'text!Amasty_GdprFrontendUi/template/components/modal/cookie-information/modal-popup.html',
    'mage/translate'
], function (
    $,
    layout,
    Modal,
    popupTpl,
    $t
) {
    'use strict';

    return Modal.extend({
        defaults: {
            template: 'Amasty_GdprFrontendUi/components/modal/cookie-information',
            name: 'gdpr-cookie-information-modal',
            showClass: '-show',
            options: {
                popupTpl: popupTpl,
                modalClass: 'amgdprcookie-groups-modal amgdprcookie-cookie-information-modal -table',
                title: null,
                headingArialLabel: $t('Cookie Group Information')
            },
            settings: {
                backgroundColor: null,
                titleTextColor: null,
                descriptionColor: null,
                tableHeaderColor: null,
                tableContentColor: null,
                doneButtonText: $.mage.__('Done'),
                doneButtonColor: null,
                doneButtonColorHover: null,
                doneButtonTextColor: null,
                doneButtonTextColorHover: null
            },
            items: [],
            groupName: null,
            description: null,
        },

        initialize: function () {
            this._super();
            this.passSettingsToModalTemplate();
        },

        initObservable: function () {
            return this._super()
                .observe({
                    items: [],
                    groupName: null,
                    description: null
                });
        },

        openModal: function (data) {
            this.groupName(data?.name ?? '');
            this.description(data?.description ?? '');
            this.items(data?.cookies ?? []);

            this._super();
        },

        passSettingsToModalTemplate: function () {
            this.options.settings = this.settings;
        }
    });
});
