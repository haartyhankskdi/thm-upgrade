/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'underscore',
    'mage/url',
    'jquery/ui'
], function ($, _, urlBuilder) {
    'use strict';
    var isChangeOption = false, getPointUrl = urlBuilder.build('mprewardpro/points/ajax');

    return function (widget) {
        $.widget('mage.priceBox', widget, {

            /**
             * Render price unit block.
             */
            reloadPrice: function reDrawPrices () {
                this._super();
                if ($('#product-options-wrapper').length && typeof this.cache.displayPrices.basePrice !== 'undefined'
                    && typeof this.cache.displayPrices.baseOldPrice !== 'undefined') {
                    var product_id   = this.options.productId,
                        basePrice    = this.cache.displayPrices.basePrice.amount,
                        baseOldPrice = this.cache.displayPrices.baseOldPrice.amount,
                        finalPrice   = this.cache.displayPrices.finalPrice.amount;
                    if (basePrice !== finalPrice || baseOldPrice !== finalPrice || isChangeOption) {
                        $.ajax({
                            url: getPointUrl,
                            type: 'GET',
                            showLoader: true,
                            data: {product_id: product_id, price: finalPrice},
                            dataType: 'json',
                            success: function (res) {
                                if (!res.has_error) {
                                    $('.catalog-points.mp-reward-points.mp-product .mp-rw-highlight').html(res.html);
                                }
                            }
                        });
                        isChangeOption = true;
                    }
                }

            }
        });

        return $.mage.priceBox;
    };
});