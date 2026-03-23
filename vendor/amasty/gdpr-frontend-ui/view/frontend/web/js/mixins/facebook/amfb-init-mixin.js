define([
    'jquery',
    'mage/utils/wrapper',
    'Amasty_FacebookPixel/js/fbq-functions'
], function ($, wrapper, fbqFunctions) {
    'use strict';

    return function (amfbInit) {
        return wrapper.wrap(amfbInit, function (originalAmfbInit, options) {
            originalAmfbInit(options);

            const gdprEvents = ['amcookie_save', 'amcookie_allow'];

            gdprEvents.forEach((event) => {
                $('body').on(event, () => fbqFunctions.initPixels(options.pixelsForCurrentStore));
            });
        });
    };
});
