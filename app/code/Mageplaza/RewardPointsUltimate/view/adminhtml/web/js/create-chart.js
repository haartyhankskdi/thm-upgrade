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

define([
    'jquery',
    'chartBundle'
], function ($) {
    'use strict';
    $.widget('mageplaza.createChart', {
        _create: function () {
            var ChartData = this.options.chartData;
            var config = {
                type: 'pie',
                data: {
                    datasets: [{
                        data: ChartData.data,
                        fill: true,

                        backgroundColor: ChartData.labelColor.colors,
                        borderWidth: 1
                    }],
                    labels: ChartData.labelColor.labels
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: ChartData.maintainAspectRatio,
                    legend: {
                        display: true,
                        position: 'left'
                    },
                    tooltips: {
                        callbacks: {}
                    }
                }
            };
            var compareDataset;

            $('.mp_menu .order_status').css('display','none');

            if (typeof window[this.options.chartElement] !== 'undefined'
                && typeof window[this.options.chartElement].destroy === 'function') {
                window[this.options.chartElement].destroy();
            }

            if (this.options.chartData.isCompare === 1) {
                compareDataset = {
                    label: ChartData.labelColor.labels,
                    data: ChartData.compareData,
                    fill: true,
                    backgroundColor: ChartData.labelColor.colors,
                    borderWidth: 1
                };
                config.data.datasets.push(compareDataset);
            }
            window[this.options.chartElement] = new Chart($('#' + this.options.chartElement), config);

        }
    });

    return $.mageplaza.createChart;
});
