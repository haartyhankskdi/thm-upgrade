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
 * @category  Mageplaza
 * @package   Mageplaza_FrequentlyBought
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
    "jquery",
    "jquery/ui",
    "downloadable"
    ], function ($) {
        "use strict";

        $.widget(
            'mageplaza.frequently_bought_downloadable', $.mage.downloadable, {
                /**
                 * Reload product price with selected link price included
                 *
                 * @private
                 */
                _reloadPrice: function () {
                    var productId,
                        finalPrice = 0,
                    basePrice = 0,
                    productPrice,
                    parentElement = this.options.usePopup === '1' ? this.element.closest('tr') : this.element.closest('li'),
                    priceAmount = this.options.usePopup === '1' ? parentElement.find('.mpfbt-product-input') : parentElement.find('.related-checkbox');
                    this.element.find(this.options.linkElement + ':checked').each(
                        $.proxy(
                            function (index, element) {
                                finalPrice += this.options.config.links[$(element).val()].finalPrice;
                                basePrice += this.options.config.links[$(element).val()].basePrice;
                            }, this
                        )
                    );

                    productPrice = parseFloat(priceAmount.attr('data-price-amount'));
                    parentElement.find('.item-price').attr('data-price-amount', productPrice + finalPrice);
                    if (this.options.usePopup === '1') {
                        productId = parentElement.attr('data-mpfbt-popup-product-id');
                        $('.item-price.mageplaza-fbt-price-' + productId).attr('data-price-amount', productPrice + finalPrice);
                    }
                    priceAmount.change();
                }
            }
        );

        return $.mageplaza.frequently_bought_downloadable;
    }
);

