define([
    'jquery'
], function ($) {
    'use strict';

    return function () {
        var containers = $('[data-amgdpr-js="container"]');

        containers.find('.amgdpr-checkbox').on('keydown', function (event) {
            var checkbox = $(event.currentTarget);

            if (event.keyCode === 13) {
                checkbox.prop('checked', !checkbox.is(':checked'));
            }
        })

        containers.first().find('.amgdpr-checkbox').focus();
    }
});
