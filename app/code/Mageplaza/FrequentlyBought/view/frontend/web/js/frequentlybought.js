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

define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'mage/translate',
    'jquery/ui'
    ], function ($, priceUtils, $t) {
        'use strict';

        $.widget(
            'mageplaza.frequentlyBought', {
                options: {
                    addWishlistUrl: '',
                    showDetails: $t('Show details'),
                    hideDetails: $t('Hide details')
                },
                cache: {
                    priceObject: {}
                },

                _create: function () {
                    this._EventListener();
                    this._bind();
                    this._renderShowDetail();
                },

                _bind: function () {
                    var self = this;
                    this.element.find('.mageplaza-fbt-rows .related-checkbox').trigger('click');
                    this.element.find('.mageplaza-fbt-rows .mageplaza-fbt-grouped').each(function () {
                        self._reloadGroupedPrice($(this));
                    });
                    if (this.options.usePopup === '1') {
                        $('.mageplaza-fbt-rows ul li').each(function () {
                            var productId = $(this).find('.related-checkbox').data('mageplaza-fbt-product-id');

                            if (!$(this).find('.related-checkbox').is(':checked')) {
                                $('#mpfbt-product-input-' + productId)[0].value = '';
                            } else {
                                $('#mpfbt-product-input-' + productId)[0].value = 1;
                            }
                        });
                    }
                },

                _EventListener: function () {
                    var self = this;
                    this.element.on(
                        'change', '.mageplaza-fbt-rows .related-checkbox', function () {
                            self._reloadTotalPrice();
                        }
                    );
                    this.element.on(
                        'change', '.mageplaza-fbt-rows .mageplaza-fbt-grouped .mageplaza-fbt-grouped-qty', function () {
                            self._reloadGroupedPrice($(this));
                        }
                    );
                    this.element.find('.mageplaza-fbt-rows .product-custom-option').on('change', this._onOptionChanged.bind(this));
                    this.element.on(
                        'click', '.mageplaza-fbt-add-to-wishlist button', function () {
                            self._addToWishList($(this));
                        }
                    );
                    this.element.on(
                        'click', '.mageplaza-fbt-detail .detailed-node', function () {
                            self._showHideDetail($(this));
                        }
                    );
                    this.element.on(
                        'click', '.mageplaza-fbt-add-to-cart button.action.mpfbt-tocart', function () {
                            if (self.options.usePopup !== '1') {
                                self._addValidation();
                            }
                        }
                    );
                },

                _addValidation: function () {
                    $('.mageplaza-fbt-rows ul li').each(
                        function () {
                            var selectTypesFlag = false,
                            $widget = $(this);
                            if (!$widget.find('.related-checkbox').is(':checked')) {
                                $widget.find('.mageplaza-fbt-detail a:not(".not-active")').click();
                                return;
                            }

                            if ($(this).find('.mageplaza-fbt-grouped').length > 0) {
                                var active = false;
                                $(this).find('.mageplaza-fbt-grouped-qty').each(
                                    function () {
                                        var qty = parseFloat($(this).val());
                                        if (qty > 0) {
                                            active = true;
                                        }
                                    }
                                );
                                if (!active) {
                                    $(this).find('.mageplaza-fbt-detail a.not-active').click();
                                }
                            }

                            $widget.find('[aria-required="true"]').each(
                                function () {
                                    var _this = this,
                                    optionType = $(_this).prop('type');
                                    if (optionType !== 'hidden' && !$(_this).hasClass('qty')) {
                                        switch (optionType) {
                                        case 'text':
                                            selectTypesFlag = $(_this).val();
                                            break;

                                        case 'textarea':
                                            selectTypesFlag = $(_this).val();
                                            break;

                                        case 'radio':
                                            selectTypesFlag = false;
                                            var nameRadio = $(_this).attr('name');
                                            $('[name="' + nameRadio + '"]').each(
                                                function () {
                                                    if ($(this).is(':checked')) {
                                                        selectTypesFlag = true;
                                                        return false;
                                                    }
                                                }
                                            );
                                                break;

                                        case 'select-one':
                                            selectTypesFlag = $(_this).val();
                                            break;

                                        case 'select-multiple':
                                            selectTypesFlag = false;
                                            _.each(
                                                $(_this).find('option'), function (option) {
                                                    if ($(option).is(':selected')) {
                                                        selectTypesFlag = true;
                                                        return false;
                                                    }
                                                }
                                            );
                                                break;

                                        case 'checkbox':
                                            selectTypesFlag = false;
                                            _.each(
                                                $(_this).closest('.mg-fbt-options-list').find('.mp-fbt-multi-select'), function (option) {
                                                    if ($(option).is(':checked')) {
                                                        selectTypesFlag = true;
                                                        return false;
                                                    }
                                                }
                                            );
                                                break;

                                        case 'file':

                                            selectTypesFlag = $(_this).val() && !$(_this).prop('disabled');
                                            break;
                                        }
                                        if (!selectTypesFlag) {
                                            return false;
                                        }
                                    }
                                }
                            );
                            if (!selectTypesFlag && $(this).find('.mageplaza-fbt-grouped').length === 0) {
                                $widget.find('.mageplaza-fbt-detail a.not-active').click();
                            }
                        }
                    );
                },

                _onOptionChanged: function (event) {
                    var optionPrice = 0,
                    changes = {},
                    element = $(event.target),
                    optionName = element.prop('name'),
                    optionType = element.prop('type'),
                    parentElement = element.closest('li'),
                    checkboxElement = parentElement.find('.related-checkbox'),
                    productId = checkboxElement.attr('data-mageplaza-fbt-product-id'),
                    productPrice = parseFloat(checkboxElement.attr('data-price-amount'));
                    switch (optionType) {
                    case 'text':

                    case 'textarea':
                        optionPrice = parseFloat(element.closest('div.field').find('.price-wrapper').attr('data-price-amount'));
                        if (element.val()) {
                            changes[optionName] = optionPrice;
                        } else {
                            changes[optionName] = 0;
                        }
                        break;

                    case 'radio':
                        optionPrice = parseFloat(element.attr('price'));
                        if (element.is(':checked')) {
                            changes[optionName] = optionPrice;
                        }
                        break;
                    case 'select-one':
                        if (element.find(":selected").attr('price')) {
                            optionPrice = parseFloat(element.find(":selected").attr('price'));
                        }
                        changes[optionName] = optionPrice;
                        break;

                    case 'select-multiple':
                        _.each(
                            element.find('option'), function (option) {
                                if ($(option).is(':selected')) {
                                    optionPrice += parseFloat($(option).attr('price'));
                                }
                            }
                        );
                        changes[optionName] = optionPrice;
                            break;

                    case 'checkbox':
                        _.each(
                            element.closest('.options-list').find('.product-custom-option'), function (option) {
                                if ($(option).is(':checked')) {
                                    optionPrice += parseFloat($(option).attr('price'));
                                }
                            }
                        );
                        changes[optionName] = optionPrice;
                            break;

                    case 'file':
                        // Checking for 'disable' property equal to checking DOMNode with id*="change-"
                        if (element.val() && !element.prop('disabled')) {
                            optionPrice = parseFloat(element.closest('div.field').find('.price-wrapper').attr('data-price-amount'));
                        }
                        changes[optionName] = optionPrice;
                        break;
                    }
                    $.extend(this.cache.priceObject, changes);
                    _.each(
                        this.cache.priceObject, function (value, key) {
                            var parentElementUpdate = $('[name="' + key + '"]').closest('li'),
                            productIdUpdate = parentElementUpdate.find('.related-checkbox').attr('data-mageplaza-fbt-product-id');
                            if (productId === productIdUpdate) {
                                productPrice += parseFloat(value);
                            }
                        }
                    );
                    parentElement.find('.item-price').attr('data-price-amount', productPrice);
                    this._reloadTotalPrice();
                },

                _reloadImageProductList: function ($this, checked) {
                    if (this.options.useSlider === true) {
                        return;
                    }
                    var productId = $this.attr('data-mageplaza-fbt-product-id');
                    if (checked) {
                        $('.mageplaza-fbt-item-' + productId).removeClass('mageplaza-fbt-hidden');
                        $('.mageplaza-fbt-plus-' + productId).removeClass('mageplaza-fbt-hidden');
                    } else {
                        $('.mageplaza-fbt-item-' + productId).addClass('mageplaza-fbt-hidden');
                        $('.mageplaza-fbt-plus-' + productId).addClass('mageplaza-fbt-hidden');
                    }
                    var elm = $('.mageplaza-fbt-image-box li:not(".mageplaza-fbt-hidden"):first');
                    if (elm && $(elm).hasClass('product-item-plus')) {
                        $(elm).addClass('mageplaza-fbt-hidden');
                    }
                },

                _reloadTotalPrice: function () {
                    var totalPrice = 0,
                    count = 0,
                    _this = this;

                    $('.mageplaza-fbt-rows .related-checkbox').each(
                        function () {
                            if ($(this).is(':checked')) {
                                totalPrice += parseFloat($(this).closest('li').find('.item-price').attr('data-price-amount'));
                                _this._reloadImageProductList($(this), true);
                                count++;
                            } else {
                                _this._reloadImageProductList($(this), false);
                            }
                            var priceElement = $(this).closest('li').find('.item-price'),
                            priceItem = $(priceElement).attr('data-price-amount');
                            $(priceElement).empty().append(_this._getFormattedPrice(priceItem));
                        }
                    );

                    var priceBox = $('.mageplaza-fbt-price-box');
                    if (count === 0) {
                        if (!priceBox.hasClass('mageplaza-fbt-hidden')) {
                            priceBox.addClass('mageplaza-fbt-hidden');
                        }
                    } else {
                        priceBox.removeClass('mageplaza-fbt-hidden');
                    }

                    $('.mageplaza-fbt-price-wrapper').attr('data-price-amount', totalPrice);
                    $('.mageplaza-fbt-price').empty().append(_this._getFormattedPrice(totalPrice));

                    _this._reloadButtonLabel(count);
                },

                _reloadButtonLabel: function (number) {
                    var buttonCartLabel = $t('Add %s to Cart'),
                    buttonWishlistLabel = $t('Add %s to Wishlist'),
                    numberLessThanTwelve = [$t('zero'), $t('one'), $t('two'), $t('three'), $t('four'), $t('five'), $t('six'), $t('seven'), $t('eight'), $t('nine'), $t('ten'), $t('eleven'), $t('twelve')],
                    num = parseInt(number, 10),
                    replace = '';

                    if (num > 1) {
                        replace = $t(' all');
                        if (num <= 12) {
                            replace += ' ' + numberLessThanTwelve[num];
                        }
                    }

                    buttonCartLabel = buttonCartLabel.replace(' %s', replace);
                    buttonWishlistLabel = buttonWishlistLabel.replace(' %s', replace);

                    $('.mageplaza-fbt-add-to-cart button').attr('title', buttonCartLabel);
                    $('.mageplaza-fbt-add-to-cart button span').empty().append(buttonCartLabel);
                    $('.mageplaza-fbt-add-to-wishlist button').attr('title', buttonWishlistLabel);
                    $('.mageplaza-fbt-add-to-wishlist button span').empty().append(buttonWishlistLabel);

                    return this;
                },

                _reloadGroupedPrice: function ($this) {
                    var _this = this,
                    totalPrice = 0,
                    productId = $this.closest('li').find('.related-checkbox').attr('data-mageplaza-fbt-product-id');
                    $('.mageplaza-fbt-rows #mageplaza-fbt-super-product-table-' + productId + ' .mageplaza-fbt-grouped-qty').each(
                        function () {
                            var price = 0;
                            if ($(this).val() > 0) {
                                price = parseFloat($(this).val()) * parseFloat($(this).attr('data-child-product-price-amount'));
                            }
                            $(this).attr('data-child-product-price-total', price);
                            totalPrice += parseFloat($(this).attr('data-child-product-price-total'));
                        }
                    );
                    $('.mageplaza-fbt-price-' + productId).attr('data-price-amount', totalPrice);

                    _this._reloadTotalPrice();
                },

                _getFormattedPrice: function (price) {
                    return priceUtils.formatPrice(price, this.options.priceFormat);
                },

                _addToWishList: function ($this) {
                    var url = $this.attr('data-url');
                    if (url) {
                        this.element.attr('action', url);
                    }
                },

                _renderShowDetail: function () {
                    var $widget = this;
                    $('.mageplaza-fbt-rows .mageplaza-fbt-option-product div').each(
                        function () {
                            var _this = this,
                            html = '';
                            if ($(_this).children().length > 0) {
                                var element = $(_this).closest('li').find('.mageplaza-fbt-detail');
                                html += '<a class="detailed-node not-active" href="javascript:void(0)">';
                                html += $widget.options.showDetails;
                                html += '</a>';
                                if ($(element).children().length === 0) {
                                    $(element).append(html);
                                }
                            }
                        }
                    );
                },

                _showHideDetail: function ($this) {
                    var $widget = this;
                    if ($this.hasClass('not-active')) {
                        $this.removeClass('not-active').empty().html($widget.options.hideDetails);
                        $this.closest('li').find('.mageplaza-fbt-option-product').removeClass('mageplaza-fbt-hidden');
                    } else {
                        $this.addClass('not-active').empty().html($widget.options.showDetails);
                        $this.closest('li').find('.mageplaza-fbt-option-product').addClass('mageplaza-fbt-hidden');
                    }
                }
            }
        );
        return $.mageplaza.frequentlyBought;
    }
);
