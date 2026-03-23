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
    'Mageplaza_RewardPointsUltimate/js/gsap.min',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mageplaza.mpRWProgress', {
        options: {
            allDescription: {},
            loadStep: 0,
            loadId: 0,
            percentBar: 0,
            allStep: 0
        },

        _create: function () {
            var self = this,
                i,
                loadLeft       = 30 - 30 * Number(self.options.loadStep),
                controlEl     = $('.mp-reward-control'),
                currentStep   = controlEl.data('step');

            TweenLite.to($('.mp-reward-step-progress'), 0.5, {
                left: loadLeft + '%',
                scale: 1,
                ease: Power1.easeInOut
            });
            for (i = 0; i < self.options.loadStep+1; i++){
                if (i < self.options.loadStep+1) {
                    TweenLite.to($('.mp-step_' + i + ' .bar'), 0.8, {width: '100%', ease: Expo.easeInOut});
                } else {
                    TweenLite.to($('.mp-step_' + i + ' .bar'), 0.8, {width: Number(self.options.percentBar) * 100 + '%', ease: Expo.easeInOut});
                }
            }
            TweenLite.to($('.mp-tier-current'), 0.5, {scale: 1.2, ease: Bounce.easeOut, delay: 0.5});
            TweenLite.to($('.progress_' + self.options.loadId + ' .bar'), 0.5, {
                width: (Number(self.options.percentBar) * 100) + '%',
                ease: Expo.easeInOut,
                delay: 0.5
            });

            if (currentStep === (Number(self.options.allStep) - 1)) {
                $('.mp-next-bar').hide();
            }

            if (currentStep === 0) {
                $('.mp-back-bar').hide();
            }

            $('.mp-reward-control div').on('click', function () {
                var currentStep = controlEl.data('step'),
                    currentEl   = $('.mp-tier[step="' + currentStep + '"]'),
                    left,
                    step= Number(self.options.allStep) - 1;

                if (!currentEl.hasClass('mp-tier-current ')) {
                    TweenLite.to(currentEl, 0.5, {scale: 1, ease: Bounce.easeOut, delay: 0.2});
                }

                if ($(this).hasClass('mp-next-bar') && currentStep < step) {
                    currentStep++;
                    left = 30 - 30 * Number(currentStep);
                    controlEl.data('step', currentStep);
                    $('.mp-back-bar').show();
                } else if ($(this).hasClass('mp-back-bar') && currentStep > 0) {
                    currentStep--;
                    left = 30 - 30 * Number(currentStep);
                    controlEl.data('step', currentStep);
                    $('.mp-next-bar').show();
                }

                if (currentStep === step || currentStep === 0) {
                    $(this).hide();
                }

                if (left !== undefined) {
                    currentEl = $('.mp-tier[step="' + currentStep + '"]');
                    if (!currentEl.hasClass('mp-tier-current ')) {
                        TweenLite.to(currentEl, 0.5, {scale: 1.2, ease: Bounce.easeOut, delay: 0.8});
                    }

                    TweenLite.to($('.mp-reward-step-progress'), 0.5, {
                        left: left + '%',
                        scale: 1,
                        ease: Power1.easeInOut
                    });
                }
            });
        }
    });

    return $.mageplaza.mpRWProgress;
});
