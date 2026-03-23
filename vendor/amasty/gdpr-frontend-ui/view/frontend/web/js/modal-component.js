/**
 * Cookie bar logic
 */

define([
    'uiCollection',
    'jquery',
    'uiRegistry',
    'Amasty_GdprFrontendUi/js/model/cookie',
    'Amasty_GdprFrontendUi/js/action/save',
    'Amasty_GdprFrontendUi/js/action/allow',
    'Amasty_GdprFrontendUi/js/model/cookie-data-provider',
    'Amasty_GdprFrontendUi/js/model/manageable-cookie',
    'Amasty_GdprFrontendUi/js/storage/essential-cookie',
    'Amasty_GdprFrontendUi/js/modal-show'
], function (
    Collection,
    $,
    registry,
    cookieModel,
    actionSave,
    actionAllow,
    cookieDataProvider,
    manageableCookie,
    essentialStorage,
    modalShowConfig
) {
    'use strict';

    return Collection.extend({
        defaults: {
            firstShowProcess: '0',
            showClass: '-show',
            isScrollBottom: false,
            isPopup: false,
            isDeclineEnabled: false,
            names: {
                setupModal: '.setup-modal',
                cookieTable: '.cookie-table'
            },
            selectors: {
                settingsFooterLink: '[data-amcookie-js="footer-link"]',
                settingsGdprLink: '[data-amgdpr-js="cookie-link"]'
            },
            popup: {
                cssClass: 'amgdprcookie-groups-modal'
            },
            templates: {
                buttons: 'Amasty_GdprFrontendUi/components/buttons',
                button: 'Amasty_GdprFrontendUi/components/button',
                toggle: 'Amasty_GdprFrontendUi/components/toggle',
                popup: 'Amasty_GdprFrontendUi/components/popup'
            },
            additionalClasses: {},
            setupModal: null,
            isAllowCustomersCloseBar: true
        },

        initialize: function () {
            this._super();

            return this;
        },

        initModalWithData: function () {
            return cookieDataProvider.getCookieData().fail(function () {
                manageableCookie.setForce(true);
                manageableCookie.processManageableCookies();
            }).done(function (cookieData) {
                manageableCookie.updateGroups(cookieData);
                manageableCookie.processManageableCookies();
                essentialStorage.update(cookieData.groupData);
                var config = modalShowConfig({isSecond: true, lastUpdate: cookieData.lastUpdate});
                cookieModel.deleteDisallowedCookie();
                cookieModel.initEventHandlers();
                this.initButtonsEvents(config.buttons);
                this.addFocusGuard();
            }.bind(this));
        },

        actionSave: function (button, elem, event) {
            event.preventDefault();
            this[button.action](elem, this);
        },

        /**
         * On save callback
         * @param {Object} element
         * @param {Object} modalContext
         */
        saveCookie: function (element, modalContext) {
            this._performSave(element, modalContext);
        },

        /**
         * Create/Open Information Modal Component.
         */
        getInformationModal: function (data) {
            this.getChild('gdpr-cookie-information-modal')?.openModal(data);
        },

        /**
         * On allow all cookies callback
         */
        allowCookies: function () {
            return actionAllow().done(function () {
                cookieModel.triggerAllow();
            }.bind(this));
        },

        declineCookie: function (element, modalContext) {
            var formData = cookieModel.getEssentialGroups();

            this._performSave(element, modalContext, formData);
        },

        _performSave: function (element, modalContext, formData) {
            return actionSave(element, formData).done(function () {
                if (modalContext.closeModal) {
                    modalContext.closeModal();
                }
            });
        },

        focus: function (element) {
            if (element.classList.contains('-save')) {
                $(element).focus();
            }
        },

        addFocusGuard: function () {
            $('.amgdprcookie-focus-guard').on('focus', ({ target, relatedTarget }) => {
                const focusTo = $(relatedTarget).is('[data-amgdprcookie-focus-start]')
                    ? 'first'
                    : $(target).data('focus-to');
                const tabbableSelector = this.selectors?.barSelector ?? this.element?.modal;
                const tabbableElements =
                    $(tabbableSelector).find(':tabbable:not(.amgdprcookie-focus-guard)');
                const switchTabToElement =
                    focusTo === 'last' ? tabbableElements.last()
                        : focusTo === 'first' ? tabbableElements.first() : null;

                switchTabToElement && $(switchTabToElement).focus();
            });
        },
    });
});
