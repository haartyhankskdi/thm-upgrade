# Hyvä Widgets

[![Hyvä Themes](https://hyva.io/media/wysiwyg/logo-compact.png)](https://hyva.io/)

## hyva-themes/magento2-hyva-widgets

![Supported Magento Versions][ico-compatibility]

This module adds set of cms widgets to work with Hyvä based themes.

Compatible with Magento 2.4.0 and higher.

## Requirements

- Magento 2.4 or higher
- Hyvä Themes version 1.1.10 or higher
- Access to Hyvä Themes via Private Packagist or gitlab.hyva.io

## Installation

1. Install via composer

   With Private Packagist access:
   ```
   # composer require hyva-themes/magento2-hyva-widgets
   ```

   \- OR -

   With Gitlab access:
   ```
   composer config repositories.hyva-themes/magento2-hyva-widgets vcs git@gitlab.hyva.io:hyva-themes/magento2-hyva-widgets.git
   composer require hyva-themes/magento2-hyva-widgets:dev-main
   ```

2. Enable module
    ```
    bin/magento module:enable Hyva_Widgets
    bin/magento setup:upgrade
    ```
   
3. If you are using a Hyvä Theme based on Hyvä 1.1.14 or before, add Tailwind Purge settings in `./app/design/frontend/[Your]/[Theme]/web/tailwind/tailwind.config.js`
    ```js
    purge: {
        content: [
            ...
            '../../../../../../../vendor/hyva-themes/magento2-hyva-widgets/**/*.phtml'
        ]
    }
    ```
   This step is not necessary for 1.1.5 and newer using automatic Tailwind config merging.  
   Then run `npm run build-prod` from the `web/tailwind` directory of your theme.

## Configuration

Requires Hyvä themes to be fully operational & set up. Please read https://docs.hyva.io/hyva-widgets/getting-started.html.

Widgets can be inserted either through the Admin at `Content > Widgets`, or via WYSYWIG editor / PageBuilder.

## Credits

- [Goran Horvat][link-author-1]
- [BEMEIR][link-author-2]
- [Willem Wigman][link-author-3]

## License
Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see [License File](LICENSE.md) for more information.

[ico-compatibility]: https://img.shields.io/badge/magento-%202.4-brightgreen.svg?logo=magento&longCache=true&style=flat-square

[link-author-1]: https://github.com/goranhorvathr
[link-author-2]: https://bemeir.com
[link-author-3]: https://github.com/wigman
