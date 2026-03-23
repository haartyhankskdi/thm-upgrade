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
 * @package     Mageplaza_FrequentlyBought
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'Mageplaza_Core/js/jquery.magnific-popup.min'
], function ($, priceUtils, customerData, $t) {
    'use strict';

    var mpFbtPopupContent = $('#mpfbt-popup-content');

    $.widget('mageplaza.fbtAjaxCart', {
        options: {
            processStart: null,
            processStop: null,
            bindSubmit: true,
            minicartSelector: '[data-block="minicart"]',
            messagesSelector: '[data-placeholder="messages"]',
            productStatusSelector: '.stock.available',
            addToCartButtonSelector: '.action.mpfbt-tocart',
            addToCartButtonDisabledClass: 'disabled',
            addToCartButtonTextWhileAdding: '',
            addToCartButtonTextAdded: '',
            addToCartButtonTextDefault: ''
        },
        cache: {
            priceObject: {}
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            var self = this;
            this._EventListener();
            if (this.options.usePopup === '1') {
                this.addToCart();
                this.element.find('#mpfbt-popup-content .mageplaza-fbt-grouped').each(function () {
                    self._reloadGroupedPrice($(this));
                });
            }
        },

        _EventListener: function () {
            var self = this;
            this.element.on(
                'change', '#mpfbt-popup-content .mpfbt-product-input', function () {
                    self._reloadTotalPrice();
                }
            );
            this.element.on(
                'change', '#mpfbt-popup-content .mageplaza-fbt-grouped .mageplaza-fbt-grouped-qty', function () {
                    self._reloadGroupedPrice($(this));
                    $('.mpfbt-total-items-value').text(self._countSelectedItems());
                }
            );
            this.element.find('#mpfbt-popup-content .product-custom-option').on('change', this._onOptionChanged.bind(this));
            $('.mageplaza-fbt-add-to-cart button.action.mpfbt-tocart').on('click', function (e) {
                if (self.options.usePopup === '1') {
                    self._showPopup(e, null);
                }
            });
            $('.mageplaza-fbt-image-box .product-item .action.tocart.primary').on('click', function (e) {
                if (self.options.usePopup === '1' && !$(this).attr('data-ajax-addtocart')) {
                    self._showPopup(e, $(this).closest('.item.product.product-item').find('.related-checkbox').data('mageplaza-fbt-product-id'));
                }
            });
        },

        _showPopup: function (event, singleProductId) {
            var self         = this,
                popupTrigger = $('#mpfbt-open-popup'),
                addToCartBtn = $('button#mpfbt-btn-addtocart');

            event.preventDefault();
            if (singleProductId !== null) {
                mpFbtPopupContent.find('.mpfbt-product-input').each(function () {
                    $(this).val('');
                });
                mpFbtPopupContent.find('.mpfbt-popup-product-detail').hide();
                $('#mpfbt-popup .mpfbt-total-items').hide();
                $('#mpfbt-popup .mageplaza-fbt-price-box').hide();
                addToCartBtn.text($t('Add to Cart'));
                addToCartBtn.prop('title', $t('Add to Cart'));

                $('#mpfbt-product-input-' + singleProductId).val(1);
                mpFbtPopupContent.find('.mpfbt-popup-product-detail[data-mpfbt-popup-product-id="'+singleProductId+'"]').show();
            } else {
                $('.mageplaza-fbt-rows ul li').each(function () {
                    var $widget = $(this),
                        productId;

                    productId = $widget.find('.related-checkbox').data('mageplaza-fbt-product-id');
                    if (!$widget.find('.related-checkbox').is(':checked')) {
                        $('#mpfbt-product-input-' + productId)[0].value = '';
                        mpFbtPopupContent.find('.mpfbt-popup-product-detail[data-mpfbt-popup-product-id="'+productId+'"]').hide();
                    } else {
                        $('#mpfbt-product-input-' + productId)[0].value = 1;
                        mpFbtPopupContent.find('.mpfbt-popup-product-detail[data-mpfbt-popup-product-id="'+productId+'"]').show();
                    }
                });
            }

            popupTrigger.magnificPopup({
                type: 'inline',
                midClick: true,
                closeBtnInside: true,
                callbacks: {
                    open: function () {
                        $('.mpfbt-total-items-value').text(self._countSelectedItems());
                        $('button.mpfbt-btn-continue').on('click', function (e) {
                            e.preventDefault();
                            $.magnificPopup.close();
                        })
                    },
                    close: function () {
                        $('#mpfbt-popup .mpfbt-total-items').show();
                        $('#mpfbt-popup .mageplaza-fbt-price-box').show();
                        addToCartBtn.text($t('Add All To Cart'));
                        addToCartBtn.prop('title', $t('Add All To Cart'));
                    }
                }
            });
            popupTrigger.click();
        },

        addToCart: function () {
            $('button#mpfbt-btn-addtocart').on('click', function (e) {
                $('.mageplaza-fbt-option-product .swatch-attribute').each(function () {
                    if ($(this).find('.swatch-select').length) {
                        let selected = $(this).attr('option-selected');
                        if (!selected) selected = $(this).attr('data-option-selected');
                        $(this).find('.super-attribute-select').val(selected);
                    }
                });
                var form      = $('#mageplaza-fbt-form-popup'),
                    actionUrl = form.attr('action'),
                    params    = form.serialize(),
                    validate  = form.validation('isValid');

                e.preventDefault();

                if (!validate) {
                    return;
                }
                $.ajax({
                    url: actionUrl,
                    data: params,
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                    success: function (res) {
                        $('.mpfbt-message').remove();
                        if (res.error) {
                            $.each(res.message, function (key, value) {
                                $('.page.messages').prepend('<div class="mpfbt-message message-error error message">' + value + '</div>');
                            });
                        }

                        if (res.success) {
                            $('.page.messages').prepend('<div class="mpfbt-message message-success success message">' + res.message + '</div>');

                            var sections = ['cart'];
                            customerData.invalidate(sections);
                            customerData.reload(sections, true);
                        }

                        $.magnificPopup.close();
                    }
                });
            });
        },

        _onOptionChanged: function (event) {
            var optionPrice = 0,
                changes = {},
                element = $(event.target),
                optionName = element.prop('name'),
                optionType = element.prop('type'),
                parentElement = element.closest('tr'),
                inputElement = parentElement.find('.mpfbt-product-input'),
                productId = inputElement.attr('data-mpfbt-popup-product-id'),
                productPrice = parseFloat(inputElement.attr('data-price-amount'));
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
                    var parentElementUpdate = $('[name="' + key + '"]').closest('tr'),
                        productIdUpdate = parentElementUpdate.find('.mpfbt-product-input').attr('data-mpfbt-popup-product-id');
                    if (productId === productIdUpdate) {
                        productPrice += parseFloat(value);
                    }
                }
            );
            parentElement.find('.item-price').attr('data-price-amount', productPrice);
            this._reloadTotalPrice();
        },

        _reloadGroupedPrice: function ($this) {
            var _this = this,
                totalPrice = 0,
                productId = $this.closest('.mpfbt-popup-product-detail').find('.mpfbt-product-input').attr('data-mpfbt-popup-product-id');
            $('#mpfbt-popup-content #mageplaza-fbt-super-product-table-' + productId + ' .mageplaza-fbt-grouped-qty').each(
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

        _reloadTotalPrice: function () {
            var totalPrice = 0,
                _this = this;

            $('#mpfbt-popup-content .mpfbt-product-input').each(
                function () {
                    if ($(this).val()) {
                        totalPrice += parseFloat($(this).closest('tr').find('.item-price').attr('data-price-amount'));
                    }
                    var priceElement = $(this).closest('tr').find('.item-price'),
                        priceItem = $(priceElement).attr('data-price-amount');
                    $(priceElement).empty().append(_this._getFormattedPrice(priceItem));
                }
            );

            $('.mageplaza-fbt-rows .related-checkbox').each(
                function () {
                    var priceElement = $(this).closest('li').find('.item-price'),
                        priceItem = $(priceElement).attr('data-price-amount');
                    $(priceElement).empty().append(_this._getFormattedPrice(priceItem));
                }
            );

            $('.mageplaza-fbt-price-wrapper').attr('data-price-amount', totalPrice);
            $('.mageplaza-fbt-price').empty().append(_this._getFormattedPrice(totalPrice));
        },

        _getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, this.options.priceFormat);
        },

        _countSelectedItems: function () {
            var count = 0;

            $('.mageplaza-fbt-rows ul li').each(function () {
                var productId = $(this).find('.related-checkbox').data('mageplaza-fbt-product-id');

                if ($(this).find('.related-checkbox').is(':checked')) {
                    var groupedItems;

                    groupedItems = mpFbtPopupContent.find('#mageplaza-fbt-super-product-table-' + productId + ' .mageplaza-fbt-grouped-qty');
                    if (groupedItems.length) {
                        groupedItems.each(function () {
                            if ($(this).val() > 0) {
                                count += parseInt($(this).val());
                            }
                        });
                    } else {
                        count++;
                    }
                }
            });

            return count;
        }
    });

    return $.mageplaza.fbtAjaxCart;
});
