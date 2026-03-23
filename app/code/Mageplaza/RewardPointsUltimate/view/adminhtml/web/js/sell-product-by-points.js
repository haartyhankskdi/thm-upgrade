/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
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
    'Magento_Ui/js/form/element/single-checkbox'
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            enableSellProduct: false,
            listens: {
                'enableSellProduct': 'toggleElement'
            }
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            var self, check;

            this._super().observe('enableSellProduct');

            self  = this;
            check = setInterval(function () {
                var rewardPoints  = $('input[name="product[mp_reward_sell_product]"]'),
                    customerGroup = $('select[name="product[mp_rw_customer_group]"]');

                if (customerGroup.length && rewardPoints.length) {
                    self.disableField([customerGroup, rewardPoints]);
                    clearInterval(check);
                }
            }, 100);

            return this;
        },

        /**
         * Disable field
         *
         * @param {Array} fields
         */
        disableField: function (fields) {
            var self = this;

            $.each(fields, function (index, field) {
                field.prop('disabled', !self.enableSellProduct());
            });
        },

        /**
         * Toggle element
         */
        toggleElement: function () {
            var rewardPoints  = $('input[name="product[mp_reward_sell_product]"]'),
                customerGroup = $('select[name="product[mp_rw_customer_group]"]');

            this.disableField([rewardPoints, customerGroup]);
        }
    });
});

