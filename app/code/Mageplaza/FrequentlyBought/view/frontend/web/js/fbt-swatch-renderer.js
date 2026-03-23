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
    'jquery',
    'underscore',
    'Magento_Swatches/js/swatch-renderer'
    ], function ($, _) {
        'use strict';

        $.widget(
            'mageplaza.FbtSwatchRenderer', $.mage.SwatchRenderer, {
                _create: function () {
                    if (this.options.usePopup === '1') {
                        this.productForm = $('#mageplaza-fbt-form-popup');
                    } else {
                        this.productForm = this.element.parents(this.options.selectorProductTile).find('form:first');
                    }
                },

                _RenderFormInput: function (config) {
                    var productId = this.options.jsonConfig.productId,
                    inputHtml = this._super(config);

                    inputHtml = inputHtml.replace('super_attribute', 'super_attribute[' + productId + ']');

                    return inputHtml;
                },

                _OnClick: function ($this, $widget) {
                    this._super($this, $widget);
                    if (this.options.usePopup === '1') {
                        if ($widget.element.closest('tr').find('.item-price').length) {
                            $widget._UpdatePrice();
                        }
                    } else {
                        if ($widget.element.closest('li').find('.item-price').length) {
                            $widget._UpdatePrice();
                        }
                    }
                },

                _UpdatePrice: function () {
                    var $widget = this,
                    $product = this.options.usePopup === '1' ? $widget.element.closest('tr') : $widget.element.closest('li'),
                    options = _.object(_.keys($widget.optionsMap), {}),
                    result;

                    $widget.element.find('.' + $widget.options.classes.attributeClass + '[data-option-selected]').each(
                        function () {
                            var attributeId = $(this).attr('data-attribute-id');

                            options[attributeId] = $(this).attr('data-option-selected');
                        }
                    );

                    result = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, options)];

                    if (result) {
                        $product.find('.item-price').attr('data-price-amount', result.finalPrice.amount);
                        if ($widget.options.usePopup === '1') {
                            $product.find('.mpfbt-product-input').attr('data-price-amount', result.finalPrice.amount).change();
                            $('.item-price.mageplaza-fbt-price-' + $product.attr('data-mpfbt-popup-product-id')).attr('data-price-amount', result.finalPrice.amount);
                            $('#mageplaza-fbt-checkbox-' + $product.attr('data-mpfbt-popup-product-id')).attr('data-price-amount', result.finalPrice.amount).change();
                        } else {
                            $product.find('.related-checkbox').attr('data-price-amount', result.finalPrice.amount).change();
                        }
                    }
                },

                _EmulateSelected: function (selectedAttributes) {},

                _LoadProductMedia: function () {},

                _loadMedia: function () {}

            }
        );

        return $.mageplaza.FbtSwatchRenderer;
    }
);

