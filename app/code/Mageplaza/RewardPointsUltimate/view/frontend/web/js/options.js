/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    return function (config) {
        var addForm           = $('div.product-add-form form#product_addtocart_form'),
            sellByPrice       = $('#mp_sell_by_price'),
            sellByPoints      = $('#mp_sell_by_points'),
            buttonSellPoints  = $('button.to_cart_by_points'),
            inputSellByPoints = '<input type="hidden" name="mp_sell_product_by" value="1">',
            labelSellByPoints = config.labelSellByPoints,
            labelSellByPrice  = config.labelSellByPrice,
            inputHtml, form,
            customer = customerData.get('customer');

        if (!customer().firstname && $('#product-updatecart-button').length) {
            inputHtml = '<div class="box-tocart mp-sell-points">';
            inputHtml += '<input type="radio" checked name="mp_sell_product_by" value="0" id="sell_by_price">';
            inputHtml += '<label class="stock" for="sell_by_price">' + labelSellByPrice + '</label></br>';
            inputHtml += '</div>';
        }else {
            inputHtml = '<div class="box-tocart mp-sell-points">';
            inputHtml += '<input type="radio" checked name="mp_sell_product_by" value="0" id="sell_by_price">';
            inputHtml += '<label class="stock" for="sell_by_price">' + labelSellByPrice + '</label></br>';
            inputHtml += '<input type="radio" name="mp_sell_product_by" value="1" id="sell_by_points">';
            inputHtml += '<label class="stock" for="sell_by_points">' + labelSellByPoints + '</label>';
            inputHtml += '</div>';
        }
        if (!addForm.find('.mp-sell-points').length) {
            addForm.prepend(inputHtml);
        }

        if (config.action === 'cms_index_index') {
            $.each(buttonSellPoints, function () {
                $(this).parent().find('form').append($(this));
            });
        }

        buttonSellPoints.on('click', function (e) {
            form = $(this).parent();
            $('input[name="mp_sell_product_by"]').remove();
            form.append(inputSellByPoints);
        });

        $('input:radio[name="mp_sell_product_by"]').change(function(){
            sellByPoints.hide();
            sellByPrice.show();

            if($(this).val() === '1'){
                sellByPoints.show();
                sellByPrice.hide();
            }
        });
    };
});
