/**
 * Cookie settings widget logic
 */
define([
    'jquery',
    'uiCollection',
    'uiRegistry',
    'Amasty_GdprFrontendUi/js/action/save',
    'Amasty_GdprFrontendUi/js/action/allow',
    'Amasty_GdprFrontendUi/js/model/cookie',
    'Amasty_GdprFrontendUi/js/storage/essential-cookie',
    'Amasty_GdprFrontendUi/js/model/cookie-data-provider',
    'Amasty_GdprFrontendUi/js/model/manageable-cookie'
], function (
    $,
    Collection,
    registry,
    actionSave,
    actionAllow,
    cookieModel,
    essentialStorage,
    cookieDataProvider,
    manageableCookie
) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: {
                name: 'Amasty_GdprFrontendUi/widget/settings'
            },
            names: {
                cookieTable: '.cookie-table'
            },
            popup: {
                cssClass: 'amgdprcookie-groups-modal'
            },
            groups: []
        },

        initialize: function () {
            this._super();

            cookieDataProvider.getCookieData().done(function (cookieData) {
                this.groups(cookieData.groupData);
                essentialStorage.update(cookieData.groupData);
                manageableCookie.updateGroups(cookieData.groupData);
                manageableCookie.processManageableCookies();
            }.bind(this)).fail(function () {
                manageableCookie.setForce(true);
                manageableCookie.processManageableCookies();
            });

            $("body").on('amcookie_save amcookie_allow', function () {
                this.refreshCookieData();
            }.bind(this));

            return this;
        },

        initObservable: function () {
            this._super()
                .observe(['groups']);

            return this;
        },

        refreshCookieData: function () {
            cookieDataProvider.updateCookieData().done(function (cookieData) {
                this.groups(cookieData.groupData);
            }.bind(this));
        },

        getInformationModal: function (data) {
            registry.get('gdpr-cookie-modal.gdpr-cookie-information-modal')?.openModal(data);
        },

        allowCookies: function () {
            actionAllow().done(function () {
                cookieModel.triggerAllow();
                this.refreshCookieData();
            }.bind(this));
        },

        saveCookie: function (element) {
            actionSave(element);
        }
    });
});
