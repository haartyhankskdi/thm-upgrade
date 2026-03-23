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
define(
    [
        'underscore',
        'Magento_Checkout/js/view/summary/item/details',
        'Mageplaza_RewardPoints/js/model/points'
    ],
    function (_, Component, points) {
        "use strict";
        var quoteItemData = window.checkoutConfig.quoteItemData;

        return Component.extend({
            defaults: {
                template: 'Mageplaza_RewardPointsUltimate/summary/item/details'
            },
            mpRewardSellPoints: '',

            /**
             * @param quoteItem
             */
            getValue: function (quoteItem) {
                return quoteItem.name;
            },

            /**
             * @param parent
             * @returns {*}
             */
            hasSellPoints: function (parent) {
                var item = _.find(quoteItemData, function (item) {
                    return item.item_id === parent.item_id;
                });

                if (item && item.mp_reward_sell_points > 0) {
                    return this.getPointLabel(item.mp_reward_sell_points * item.qty);
                }
            },

            /**
             * @param points
             */
            getPointLabel: function (value) {

                return this.mpRewardSellPoints = points.format(value);
            }
        });
    }
);
