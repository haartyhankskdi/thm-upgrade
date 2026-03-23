define([
    'jquery',
    'amSplide413',
    'splideStyleLoader'
], function ($, Splide, splideStyleLoader) {
    'use strict';

    return function (config) {
        splideStyleLoader('4.1.3').then(() => {
            const splideSliderSelector = config.selector + ' .splide';
            const splideSlider = new Splide(splideSliderSelector, {
                perPage: config.itemsPerView,
                arrows: config.isButtonsShow,
                type: config.isLoop ? 'loop' : false,
                drag: config.simulateTouch,
                breakpoints: config.breakpoints,
                pagination: config.showPagination,
                autoplay: config.autoplay,
                interval: config.autoplayTime,
                slideFocus: false,
                focusableNodes: 'a',
                updateOnMove: true,
                classes: {
                    arrow: 'splide__arrow am-brand-splide-arrow',
                    prev: 'splide__arrow--prev am-brand-splide-arrow-prev',
                    next: 'splide__arrow--next am-brand-splide-arrow-next',
                    pagination: 'splide__pagination am-brand-splide-pagination',
                    page: 'splide__pagination__page am-brand-splide-page',
                }
            });
            splideSlider.mount();

            $(splideSliderSelector).removeClass('ambrands-slider-hidden');
        });
    };
});

