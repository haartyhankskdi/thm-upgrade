define([
    'Magento_Ui/js/form/element/region'
], function (Region) {
    'use strict';

    return Region.extend({
        defaults: {
            skipValidation: false,
            imports: {
                countryOptions: '${ $.parentName }.country:indexedOptions',
                update: '${ $.parentName }.country:value'
            }
        }
    });
});
