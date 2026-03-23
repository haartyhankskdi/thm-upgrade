/**
 * Cookie Need Show
 */

define([
    'jquery',
    'mage/cookies',
    'jquery/jquery-storageapi'
], function ($) {
    'use strict';

    return {
        isShowNotificationBarBefore: function (firstShowProcess) {
            return this.isNeedFirstShow(firstShowProcess)
                && $.mage.cookies.get('amcookie_allowed') === null;
        },

        isShowNotificationBarAfter: function (lastUpdate) {
            $.localStorage.set('amCookieBarFirstShowTime', lastUpdate);

            return this.isNeedShowOnUpdate(lastUpdate)
        },

        isNeedFirstShow: function (firstShowProcess, lastUpdate) {
            if (firstShowProcess === '0') {
                return true;
            }

            if (!$.localStorage.get('amCookieBarFirstShow')) {
                $.localStorage.set('amCookieBarFirstShow', 1);

                return true;
            }

            return false;
        },

        isNeedShowOnUpdate: function (lastUpdate) {
            if (!lastUpdate) {
                return true;
            }

            if ($.localStorage.get('amCookieBarFirstShow')) {
                return false;
            }

            return this.isNeedShowAfterLastVisit(lastUpdate) || this.isNeedShowAfterLastAccept(lastUpdate)
        },

        isNeedShowAfterLastVisit: function (lastUpdate) {
            var needToShowAfterLastVisit = lastUpdate > $.localStorage.get('amCookieBarFirstShowTime');

            if (needToShowAfterLastVisit) {
                $.localStorage.set('amCookieBarFirstShow', null);
                $.mage.cookies.clear('amcookie_allowed');
            }

            return needToShowAfterLastVisit;
        },

        isNeedShowAfterLastAccept: function (lastUpdate) {
            var needToShowAfterLastAccept = false;

            if ($.localStorage.get('am-last-cookie-acceptance')) {
                needToShowAfterLastAccept = lastUpdate > $.localStorage.get('am-last-cookie-acceptance');
            }

            return needToShowAfterLastAccept;
        }
    };
});
