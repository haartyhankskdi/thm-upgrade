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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    var widgetMixin = {
        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnClick: function ($this, $widget) {
            this._super($this, $widget);

            var allowedProduct  = $widget._getAllowedProductWithMinPrice($widget._CalcProducts()),
                sellPoints      = $widget.options.jsonConfig.sellPoints,
                sellPointsPrice = $('span#mp_sell_by_points span.price-container.price-final_price').find('.price'),
                defaultLable    = $widget.options.jsonConfig.defaultPoints;

            sellPointsPrice.text(defaultLable);
            if (sellPoints) {
                sellPoints.forEach(function (item) {
                    if (allowedProduct.length && allowedProduct === item['id']) {
                        sellPointsPrice.text(item['points']);
                    }
                });
            }

        },
    };

    return function (parentWidget) {
        $.widget('mage.SwatchRenderer', parentWidget, widgetMixin);

        return $.mage.SwatchRenderer;
    };
});
