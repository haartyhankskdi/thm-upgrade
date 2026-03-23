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
        'mage/template',
        'priceUtils',
        'priceBox',
        'priceBundle'
    ], function ($, _, mageTemplate, utils) {
        'use strict';

        $.widget(
            'mageplaza.frequently_bought_bundle_option', $.mage.priceBundle, {

                _onBundleOptionChanged: function onBundleOptionChanged (event) {
                    var changes,
                        $key,
                        bundleOption = $(event.target),
                        handler      = this.options.optionHandlers[bundleOption.data('role')];

                    bundleOption.data('optionContainer', bundleOption.closest(this.options.controlContainer));
                    bundleOption.data('qtyField', bundleOption.data('optionContainer').find(this.options.qtyFieldSelector));

                    if (handler && handler instanceof Function) {
                        changes = handler(bundleOption, this.options.optionConfig, this);
                    } else {
                        changes = defaultGetOptionValue(bundleOption, this.options);
                    }

                    if (changes) {
                        $key = Object.keys(changes)[0];
                        if ($key && changes[$key].finalPrice && bundleOption.prop('type') !== 'checkbox' && bundleOption.prop('type') !== 'select-multiple') {
                            $('#' + $key).attr('data-price-amount', changes[$key].finalPrice.amount);
                        }
                    }
                    this._updateProductPrice($('#' + $key));
                },

                _onQtyFieldChanged: function onQtyFieldChanged (event) {
                    this._super(event);

                    var field = $(event.target),
                        qty   = 0,
                        price = 0;

                    if (!field.data('optionId') || !field.data('optionValueId')) {
                        qty = parseFloat(field.val());
                        if (!qty || qty < 0) {
                            qty = 1;
                        }
                        price = parseFloat(field.closest('.options-list').find('.price-wrapper').attr('data-price-amount'));
                        field.closest('.mg-fbt-field-option').attr('data-price-amount', qty * price);
                    }
                    this._updateProductPrice(field);
                },

                _updateProductPrice: function (element) {
                    var totalPrice = this.options.optionConfig.prices.finalPrice['amount'],
                        productId;

                    element.closest('.bundle-options-container').find('.mg-fbt-field-option').each(
                        function () {
                            totalPrice += parseFloat($(this).attr('data-price-amount'));
                        }
                    );
                    if (this.options.usePopup === '1') {
                        element.closest('tr').find('.item-price').attr('data-price-amount', totalPrice);
                        productId = element.closest('tr').attr('data-mpfbt-popup-product-id');
                        $('.item-price.mageplaza-fbt-price-' + productId).attr('data-price-amount', totalPrice);
                        element.closest('tr').find('.mpfbt-product-input').attr('data-price-amount', totalPrice).change();
                    } else {
                        element.closest('li').find('.item-price').attr('data-price-amount', totalPrice);
                        element.closest('li').find('.related-checkbox').attr('data-price-amount', totalPrice).change();
                    }
                },

                _applyQtyFix: function applyQtyFix () {
                },

                _applyOptionNodeFix: function applyOptionNodeFix (options) {
                },

                updateProductSummary: function () {
                }
            }
        );

        return $.mageplaza.frequently_bought_bundle_option;

        /**
         * Converts option value to priceBox object
         *
         * @param   {jQuery} element
         * @param   {Object} options
         * @returns {Object|null} - priceBox object with additional prices
         */
        function defaultGetOptionValue (element, options) {
            var changes         = {},
                config          = options.optionConfig,
                usePopup        = options.usePopup,
                optionHash,
                priceOption     = 0,
                tempChanges,
                qtyField,
                optionId        = parseInt($(element).attr('data-option-id')),
                optionValue     = element.val() || null,
                optionType      = element.prop('type'),
                productId       = usePopup === '1' ? element.closest('tr').find('.mpfbt-product-input').attr('data-mpfbt-popup-product-id') : element.closest('li').find('.related-checkbox').attr('data-mageplaza-fbt-product-id'),
                optionConfig    = config.options[optionId].selections,
                optionQty       = 0,
                canQtyCustomize = false,
                selectedIds     = config.selected;

            switch (optionType){
                case 'radio':
                case 'select-one':
                    if (optionType === 'radio' && !element.is(':checked')) {
                        return null;
                    }

                    qtyField = element.data('qtyField');
                    qtyField.data('option', element);

                    if (optionValue) {
                        optionQty       = optionConfig[optionValue].qty || 0;
                        canQtyCustomize = optionConfig[optionValue].customQty === '1';
                        toggleQtyField(qtyField, optionQty, optionId, optionValue, canQtyCustomize);
                        tempChanges = utils.deepClone(optionConfig[optionValue].prices);
                        tempChanges = applyTierPrice(tempChanges, optionQty, optionConfig[optionValue]);
                        tempChanges = applyQty(tempChanges, optionQty);
                    } else {
                        tempChanges = {};
                        toggleQtyField(qtyField, '0', optionId, optionValue, false);
                    }
                    optionHash            = 'mp-fbt-bundle-option-' + productId + '-' + optionId;//'bundle-option-' + optionName;
                    changes[optionHash]   = tempChanges;
                    selectedIds[optionId] = [optionValue];
                    break;

                case 'select-multiple':
                    optionValue = _.compact(optionValue);
                    optionHash  = 'mp-fbt-bundle-option-' + productId + '-' + optionId;
                    _.each(
                        optionConfig, function (row, optionValueCode) {
                            optionQty   = row.qty || 0;
                            tempChanges = utils.deepClone(row.prices);
                            tempChanges = applyTierPrice(tempChanges, optionQty, optionConfig);
                            tempChanges = applyQty(tempChanges, optionQty);
                            if (_.contains(optionValue, optionValueCode)) {
                                changes[optionHash] = tempChanges;
                                priceOption += tempChanges.finalPrice.amount;
                            } else {
                                changes[optionHash] = {};
                            }
                        }
                    );
                    $('#' + optionHash).attr('data-price-amount', priceOption);
                    selectedIds[optionId] = optionValue || [];
                    break;

                case 'checkbox':
                    optionHash  = 'mp-fbt-bundle-option-' + productId + '-' + optionId;//'bundle-option-' + optionName + '##' + optionValue;
                    optionQty   = optionConfig[optionValue].qty || 0;
                    tempChanges = utils.deepClone(optionConfig[optionValue].prices);
                    tempChanges = applyTierPrice(tempChanges, optionQty, optionConfig);
                    tempChanges = applyQty(tempChanges, optionQty);
                    if (element.is(':checked')) {
                        changes[optionHash] = tempChanges;
                        element.attr('data-price-amount', tempChanges.finalPrice.amount);
                    } else {
                        changes[optionHash] = {};
                        element.attr('data-price-amount', 0);
                    }
                    reloadMultiSelect(element);
                    selectedIds[optionId] = selectedIds[optionId] || [];

                    if (!_.contains(selectedIds[optionId], optionValue) && element.is(':checked')) {
                        selectedIds[optionId].push(optionValue);
                    } else if (!element.is(':checked')) {
                        selectedIds[optionId] = _.without(selectedIds[optionId], optionValue);
                    }
                    break;

                case 'hidden':
                    optionHash            = 'mp-fbt-bundle-option-' + productId + '-' + optionId;
                    optionQty             = optionConfig[optionValue].qty || 0;
                    tempChanges           = utils.deepClone(optionConfig[optionValue].prices);
                    tempChanges           = applyTierPrice(tempChanges, optionQty, optionConfig);
                    tempChanges           = applyQty(tempChanges, optionQty);
                    optionHash            = 'mp-fbt-bundle-option-' + productId + '-' + optionId;
                    changes[optionHash]   = tempChanges;
                    selectedIds[optionId] = [optionValue];
                    break;
            }

            return changes;
        }

        function reloadMultiSelect (element) {
            var priceProduct = 0;
            element.closest('.mg-fbt-options-list').find('.mp-fbt-multi-select').each(
                function () {
                    priceProduct += parseFloat($(this).attr('data-price-amount'));
                }
            );
            element.closest('.mg-fbt-field-option').attr('data-price-amount', priceProduct);
        }

        /**
         * Helper to toggle qty field
         *
         * @param {jQuery} element
         * @param {String|Number} value
         * @param {String|Number} optionId
         * @param {String|Number} optionValueId
         * @param {Boolean} canEdit
         */
        function toggleQtyField (element, value, optionId, optionValueId, canEdit) {
            element
                .val(value)
                .data('optionId', optionId)
                .data('optionValueId', optionValueId)
                .attr('disabled', !canEdit);

            if (canEdit) {
                element.removeClass('qty-disabled');
            } else {
                element.addClass('qty-disabled');
            }
        }

        /**
         * Helper to multiply on qty
         *
         * @param   {Object} prices
         * @param   {Number} qty
         * @returns {Object}
         */
        function applyQty (prices, qty) {
            _.each(
                prices, function (everyPrice) {
                    everyPrice.amount *= qty;
                    _.each(
                        everyPrice.adjustments, function (el, index) {
                            everyPrice.adjustments[index] *= qty;
                        }
                    );
                }
            );

            return prices;
        }

        /**
         * Helper to limit price with tier price
         *
         * @param   {Object} oneItemPrice
         * @param   {Number} qty
         * @param   {Object} optionConfig
         * @returns {Object}
         */
        function applyTierPrice (oneItemPrice, qty, optionConfig) {
            var tiers    = optionConfig.tierPrice,
                magicKey = _.keys(oneItemPrice)[0],
                lowest   = false;

            _.each(
                tiers, function (tier, index) {
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    if (tier.price_qty > qty) {
                        return;
                    }
                    // jscs:enable requireCamelCaseOrUpperCaseIdentifiers

                    if (tier.prices[magicKey].amount < oneItemPrice[magicKey].amount) {
                        lowest = index;
                    }
                }
            );

            if (lowest !== false) {
                oneItemPrice = utils.deepClone(tiers[lowest].prices);
            }

            return oneItemPrice;
        }
    }
);
