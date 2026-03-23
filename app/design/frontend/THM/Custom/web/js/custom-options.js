define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'Magento_Catalog/js/price-box',
    'Magento_Catalog/js/catalog-add-to-cart'
], function ($, utils) {
    'use strict';

    return function (config) {
        $(document).ready(function () {
            var $select = $('#select_' + config.optionId);

            $('.custom-option-tabs[data-option-id="' + config.optionId + '"] .custom-option-btn').on('click', function () {
                var $btn = $(this);
                var selectedValue = $btn.data('value');

                // Make button active
                $btn.siblings().removeClass('active');
                $btn.addClass('active');

                // Update select input value
                $select.val(selectedValue).trigger('change');

                // Update price using Magento’s method
                if (typeof window.customOptions !== 'undefined' && typeof window.customOptions.reloadPrice === 'function') {
                    window.customOptions.reloadPrice();
                } else {
                    $('.product-info-main').trigger('updatePrice');
                }
            });
        });
    };
});
