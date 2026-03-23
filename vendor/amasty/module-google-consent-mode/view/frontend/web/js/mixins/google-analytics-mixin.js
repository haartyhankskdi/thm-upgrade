define([
    'mage/utils/wrapper',
    'Amasty_GdprFrontendUi/js/action/ga-initialize'
], function (wrapper, gaInitialize) {
    'use strict';

    return function (initializeGtm) {
        return wrapper.wrap(initializeGtm, function (originalInitializeGa, config) {
            originalInitializeGa(config);

            if (!gaInitialize.deferrer.resolve) {
                gaInitialize.initialize(config);
            }

            gaInitialize.deferrer.resolve();
        });
    };
});
