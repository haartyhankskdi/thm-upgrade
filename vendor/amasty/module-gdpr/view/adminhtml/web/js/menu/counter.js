define([
    'jquery'
], function ($) {
    'use strict';

    return function (deleteRequestCount) {
        const gdpr = $('.item-gdpr'),
            amastymenu = $('#menu-amasty-gdpr-container');

        if (gdpr.length) {
            const requests = gdpr.find('.item-requests');
            if (requests.length && deleteRequestCount > 0) {
                requests.find('a').html(requests.find('a').text() + ' <span class="amgdpr-status">' + deleteRequestCount + '</span>');
            }
        }

        if (amastymenu.length) {
            const amastymanage = amastymenu.find('.item-requestsmenu');
            if (amastymanage.length && deleteRequestCount > 0) {
                amastymanage.find('a').html(amastymanage.find('a').text() + ' <span class="amgdpr-status">' + deleteRequestCount + '</span>');
            }
        }
    }
});
