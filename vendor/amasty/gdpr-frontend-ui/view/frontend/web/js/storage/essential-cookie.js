/**
 * Essential Cookie Storage
 */

define([
    'underscore'
], function (_) {
    'use strict';

    return {
        cookies: [],

        /**
         * Is Essential Cookie
         * @param {string} cookieName
         */
        isEssential: function (cookieName) {
            return this.cookies.indexOf(cookieName) !== -1;
        },

        /**
         * Update Essential Cookie
         * @param {array} groups
         */
        update: function (groups) {
            if (!this.cookies.length) {
                _.each(groups, function (group) {
                    if (group.isEssential) {
                        this.set(group.cookies);
                    }
                }.bind(this));
            }
        },

        /**
         * Set Essential Cookie
         * @param {array} cookies
         */
        set: function (cookies) {
            cookies.forEach(function (item) {
                this.cookies.push(item.name);
            }.bind(this));
        },

        /**
         * Get Essential Cookie Pattern
         * @param {string} pattern
         * @return {Array}
         */
        getEssentialCookiesPattern: function (pattern) {
            let result = null,
                cookiePatterns = [];

            this.cookies.forEach((cookie) => {
                result = cookie.match(pattern);

                if (result !== null) {
                    cookiePatterns.push(result.groups.cookiePattern);
                }
            });

            return cookiePatterns;
        },

        /**
         * Is Cookie Essential By Pattern
         * @param {string} cookieName
         * @param {string} pattern
         * @return {boolean}
         */
        isEssentialByPattern: function (cookieName, pattern) {
            const essentialCookiePatterns = this.getEssentialCookiesPattern(pattern);

            return essentialCookiePatterns.some((pattern) => !!cookieName.match(`^${pattern.replaceAll('*', '.*')}$`));
        },
    };
});
