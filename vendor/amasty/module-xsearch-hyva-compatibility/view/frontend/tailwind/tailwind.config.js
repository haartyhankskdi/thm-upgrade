/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 */

module.exports = {
    content: [
        '../templates/**/*.phtml',
    ],
    theme: {
        extend: {
            maxWidth: {
                'am-search-sidebar': '20%',
                'am-search-content': '80%',
            },
            flex: {
                'am-search-sidebar': '0 1 100%',
            },
            height: {
                'carousel-product-item': '540px',
            },
            zIndex: {
                '110': '110',
                '9': '9',
                '8': '8'
            }
        },
    }
}
