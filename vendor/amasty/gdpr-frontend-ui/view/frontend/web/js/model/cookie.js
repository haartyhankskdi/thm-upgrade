/**
 * Cookie Model
 */

define([
    'jquery',
    'underscore',
    'Amasty_GdprFrontendUi/js/model/cookie-data-provider',
    'Amasty_GdprFrontendUi/js/storage/cookie',
    'Amasty_GdprFrontendUi/js/storage/essential-cookie',
    'Amasty_GdprFrontendUi/js/action/ga-initialize',
    'mage/cookies',
    'jquery/jquery-storageapi'
], function ($, _, cookieDataProvider, cookieStorage, essentialStorage) {
    'use strict';

    return {
        options: {
            pattern: '{(?<cookiePattern>.*)}'
        },

        initEventHandlers: function () {
            var body = $('body');

            body.on('amcookie_save', function () {
                this.setLastCookieAcceptance();
            }.bind(this));
            body.on('amcookie_allow', function () {
                this.setLastCookieAcceptance();
            }.bind(this));
        },

        deleteDisallowedCookie: function () {
            const disallowedCookie = $.mage.cookies.get('amcookie_disallowed');

            if (!disallowedCookie) {
                return;
            }

            disallowedCookie.split(',').forEach((name) => {
                const pattern = name.match(this.options.pattern);

                if (!!pattern) {
                    cookieStorage.getCookiesByPattern(this.getCookiePattern(pattern.groups.cookiePattern))
                        .forEach((cookieName) => {
                            this.deleteNotEssentialCookie(cookieName);
                        })
                } else {
                    this.deleteNotEssentialCookie(name);
                }
            });
        },

        getEssentialGroups: function () {
            var groups,
                filteredGroups;

            cookieDataProvider.getCookieData().done(function (cookieData) {
                groups = cookieData;
            });

            filteredGroups = _.filter(groups, function (group) {
                return group.isEssential;
            });

            return {
                'groups': filteredGroups.map(function (group) {
                    return group.groupId;
                })
            };
        },

        isCookieAllowed: function (cookieName) {
            var allowedGroups = $.mage.cookies.get('amcookie_allowed'),
                disallowedCookie = $.mage.cookies.get('amcookie_disallowed') || '',
                isCookiePolicyAllowed = $.mage.cookies.get('amcookie_policy_restriction') === 'allowed';

            if (!isCookiePolicyAllowed
                || essentialStorage.isEssential(cookieName)
                || essentialStorage.isEssentialByPattern(cookieName, this.options.pattern)
            ) {
                return true;
            }

            return !((!allowedGroups && !disallowedCookie) || this.isCookieDisallowed(cookieName));
        },

        setLastCookieAcceptance: function () {
            cookieDataProvider.getCookieData().done(function (cookieData) {
                $.localStorage.set('am-last-cookie-acceptance', cookieData.lastUpdate);
            });
        },

        triggerSave: function () {
            $('body').trigger('amcookie_save');
        },

        triggerAllow: function () {
            $('body').trigger('amcookie_allow');
        },

        isCookieDisallowed: function (cookieName) {
            const disallowedCookies = $.mage.cookies.get('amcookie_disallowed')?.split(',') ?? [];

            return disallowedCookies.some((cookie) => {
                    const pattern = cookie.match(this.options.pattern);

                    return !!pattern && !!cookieName.match(this.getCookiePattern(pattern.groups.cookiePattern));
                }) || disallowedCookies.indexOf(cookieName) !== -1;
        },

        deleteNotEssentialCookie: function (cookieName) {
            if (!essentialStorage.isEssential(cookieName)) {
                cookieStorage.delete(cookieName);
            }
        },

        getCookiePattern: function (pattern) {
            return `^${pattern.replaceAll('*', '.*')}$`;
        },
    };
});
