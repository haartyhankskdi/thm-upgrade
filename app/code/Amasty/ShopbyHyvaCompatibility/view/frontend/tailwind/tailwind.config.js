/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Amasty Improved Layered Navigation Hyvä compatibility for Magento 2
 */
module.exports = {
    content: [
        '../templates/**/*.phtml',
    ],

    theme: {
        extend: {
            colors: {
                'amsb_graystarts': '#cbd5e0',
                'amsb_yellowstars': '#f6e05e',
                'amsb_sl_gray': '#dadada',
                'amsb_sl_gray_1': '#b6b6b6',
                'amsb_sl_gray_2': '#dfdedd',
                'amsb_sl_gray_3': '#4a4948'
            },
            zIndex : {
                '5': '5',
                '15': '15',
                '25': '25',
                '80': '80',
                '90': '90',
                '100': '100'
            },
            maxWidth: {
                '100-50': 'calc(100% - 50px)',
            },
            transitionProperty: {
                'left-top': 'left, top',
            },
        },
    }
}
