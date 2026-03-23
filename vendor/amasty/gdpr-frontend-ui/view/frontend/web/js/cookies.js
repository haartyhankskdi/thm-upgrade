/**
 * Cookie bar logic
 */

define([
    'Amasty_GdprFrontendUi/js/modal-component',
    'jquery',
    'mage/translate',
    'Amasty_GdprFrontendUi/js/model/cookie-data-provider'
], function (
    ModalComponent,
    $,
    $t,
    cookieDataProvider,
) {
    'use strict';

    return ModalComponent.extend({
        defaults: {
            template: 'Amasty_GdprFrontendUi/components/elems',
            allowLink: '/',
            firstShowProcess: '0',
            cookiesName: [],
            domainName: '',
            setupModalTitle: $t('Please select and accept your Cookies Group'),
            isPopup: false,
            isDeclineEnabled: false,
            barLocation: null,
            barType: null,
            selectors: {
                barSelector: '[data-amcookie-js="bar"]',
                acceptButton: '[data-amgdprcookie-js="accept"]',
                closeCookieBarButton: '[data-amcookie-js="close-cookiebar"]',
                overlay: '[data-amgdpr-js="overlay"]',
                focusStartSelector: '[data-amgdprcookie-focus-start]'
            },
            availableBarTypes: {
                classic: 0,
                sidebar: 1,
                popup: 2
            },
            availableBarLocations: {
                footer: 0,
                top: 1
            }
        },

        initialize: function () {
            this._super();

            this.initEventHandlers();
            this.initModalWithData();

            return this;
        },

        initEventHandlers: function () {
            if (this.isAllowCustomersCloseBar) {
                $(this.selectors.closeCookieBarButton).on('click', this.closeCookieBar.bind(this));
                this.closeOnEscapeButton();
            }
        },

        closeOnEscapeButton: function () {
            const closeEvent = (event) => {
                if (event.keyCode === 27) {
                    this.closeCookieBar.call(this);
                    $(document).off('keydown', this.selectors.barSelector, closeEvent);
                }
            };

            $(document).on('keydown',  this.selectors.barSelector, closeEvent);
        },

        initButtonsEvents: function (buttons) {
            buttons.forEach(function (button) {
                if (button.dataJs !== 'settings') {
                    var elem = $('[data-amgdprcookie-js="' + button.dataJs + '"]');
                    elem.on('click', this.actionSave.bind(this, button, elem));
                    elem.attr('disabled', false);
                } else {
                    $('[data-amgdprcookie-js="' + button.dataJs + '"]')
                        .attr('disabled', false)
                        .on('click', this.openCookieSettingsModal.bind(this));
                }
            }.bind(this));

            this.setInitialFocus();
        },

        openCookieSettingsModal: function () {
            this.getChild('gdpr-cookie-settings-modal').openModal();
        },

        /**
         * Setting focus to the initial popup element
         * so that the screen reader starts reading the content when the page loads.
         * Excluding the classic bar in the page footer.
         * It should be read in the general flow of the page.
         *
         * @return {void}
         */
        setInitialFocus: function () {
            const $focusStartNode = $(this.selectors.focusStartSelector);

            if (this.isClassicBarInFooter()) {
                $focusStartNode.removeAttr('tabindex');
            } else {
                $focusStartNode.trigger('focus');
                $focusStartNode.one('focusout', function () {
                    $(this).removeAttr('tabindex');
                });
            }
        },

        /**
         * @return {Boolean}
         */
        isClassicBarInFooter: function () {
            return this.barType === this.availableBarTypes.classic
                && this.barLocation === this.availableBarLocations.footer;
        },

        /**
         * On allow all cookies callback
         */
        allowCookies: function () {
            this._super().done(function () {
                this.closeCookieBar();
            }.bind(this));
        },

        _performSave: function () {
            this._super();

            this.closeCookieBar();
        },

        closeCookieBar: function () {
            $(this.selectors.barSelector).remove();
            $(this.selectors.overlay).remove();
        }
    });
});
