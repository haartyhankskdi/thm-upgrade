/**
 * Cookie Storage
 */

define([], function () {
    'use strict';

    return {
        /**
         * Set Cookie
         * @param {string} name
         * @param {string} value
         * @param {Object} options
         */
        set: function (name, value, options) {
            var updatedCookie = encodeURIComponent(name) + '=' + encodeURIComponent(value),
                optionKey,
                optionValue;

            if (typeof options.expires === 'number') {
                options.expires = new Date(Date.now() + options.expires * 864e5);
            }

            if (options.expires) {
                options.expires = options.expires.toUTCString();
            }


            for (optionKey in options) {
                updatedCookie += '; ' + optionKey;
                optionValue = options[optionKey];

                if (optionValue !== true) {
                    updatedCookie += '=' + optionValue;
                }
            }

            document.cookie = updatedCookie;
        },

        /**
         * Delete Cookie
         * @param {string} name
         */
        delete: function (name) {
            this.set(name, '', {
                'max-age': -1,
                'path': '/',
                'expires': -1
            });
        },

        /**
         * get All Cookies
         * @return {Array}
         */
        getAllCookies: function () {
            return document.cookie.split(';');
        },

        /**
         * get Cookies By Pattern
         * @param {string} pattern
         * @param {Array} cookies
         * @return {Array}
         */
        getCookiesByPattern: function (pattern, cookies) {
            let findCookies = [];
            const cookiesArray = cookies ?? this.getAllCookies();

            cookiesArray.forEach((cookie) => {
                const match = cookie.split('=')[0].trim().match(pattern);

                if (match !== null) {
                    findCookies.push(match.input);
                }
            });

            return findCookies;
        }
    };
});
