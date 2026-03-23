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

/**
 * @returns {string}
 */
function getCreateChart () {
    var prefix = document.getElementsByClassName("mp_menu").length ? '' : 'fake-';

    return 'Mageplaza_RewardPointsUltimate/js/' + prefix + 'create-chart';
}

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/grid/provider',
    'uiRegistry',
    getCreateChart()
], function ($, _, Provider, uiRegistry, createChart) {
    'use strict';

    return Provider.extend({
        reload: function (options) {
            this.addParamsToFilter();
            this._super();
        },

        /**
         * Compatible with Mageplaza Reportspro
         */
        addParamsToFilter: function () {
            var mpFilter = {};

            if (this.isEnableReportMenu()) {
                $('.admin__data-grid-header .data-grid-filters-actions-wrap').hide();
                mpFilter = typeof this.params.mpFilter === "undefined" ? {} : this.params.mpFilter;

                if (typeof mpFilter.startDate === "undefined") {
                    mpFilter.startDate = $('#daterange').data().startDate.format('Y-MM-DD');
                }
                if (typeof mpFilter.endDate === "undefined") {
                    mpFilter.endDate = $('#daterange').data().endDate.format('Y-MM-DD');
                }
                if (typeof mpFilter.period === "undefined") {
                    mpFilter.period = $('.period select').val();
                }
                if (typeof mpFilter.store === "undefined") {
                    mpFilter.store = $('#store_switcher').val();
                }
                if (typeof mpFilter.customer_group_id === "undefined") {
                    mpFilter.customer_group_id = $('.customer-group select').val();
                }
                this.params.mpFilter = mpFilter;
            }
        },

        /**
         * @param data
         * @returns {*}
         */
        processData: function (data) {
            this.buildChart(data);

            return this._super();
        },

        /**
         * @returns {string[]}
         */
        getMpFields: function () {
            return [
                'admin',
                'earning_order',
                'earning_sign_up',
                'earning_newsletter_subscriber',
                'earning_review_product',
                'earning_customer_birthday',
                'earning_like_facebook',
                'earning_share_facebook',
                'earning_tweet_twitter',
                'referral_earning'
            ];
        },

        /**
         * Build chart when Mp Reports enable
         */
        buildChart: function (data) {
            var items      = {},
                rewardData = [],
                mpFields;

            if (this.isEnableReportMenu() && $('body').hasClass('mprewardultimate-reports-earned')) {
                items = data.items;
                if (Object.keys(items).length) {
                    rewardData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                    mpFields   = this.getMpFields();
                    _.each(items, function (record, index) {

                        _.each(mpFields, function (val, key) {
                            rewardData[key] += parseInt(record[val]);
                        });
                    });
                    if (rewardData[0] < 0) {
                        rewardData[0] = 0;
                    }
                    createChart({
                        chartData: {
                            labelColor: this.labelColor,
                            data: rewardData,
                            maintainAspectRatio: true
                        },
                        chartElement: 'earned-chart'
                    });
                    $('#earned-chart').show();
                } else {
                    $('#earned-chart').hide();
                }
            }
        },

        /**
         * @returns {jQuery}
         */
        isEnableReportMenu: function () {
            return $('.mp_menu').length;
        }
    });
});
