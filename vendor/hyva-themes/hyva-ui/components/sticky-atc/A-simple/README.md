# Hyvä UI - sticky-atc.A - simple

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![Figma]](https://www.figma.com/@hyva)

Streamline your shopping experience with a user-friendly sticky Add To Cart component.

This component allows customers to add products to their cart without scrolling back to the top of the page, enhancing the overall shopping experience.

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Catalog/templates/product/view/addtocart-sticky.phtml`
   * `Magento_Catalog/layout/catalog_product_view.xml`
   * `Magento_Checkout/layout/checkout_cart_configure.xml`
2. Adjust the content and code to fit your own needs and save
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Catalog/layout/catalog_product_view.xml` file.

| Option Name                   | Type    | Available Values | Default | Description                                    |
| ----------------------------- | ------- | ---------------- | ------- | ---------------------------------------------- |
| `mobile_only`                 | boolean | true, false      | true    | Hide the Sticky.ATC on desktop                 |
| `truncate_title`              | boolean | true, false      | true    | Don't allow the title to wrap                  |
| `only_show_after_add_to_cart` | boolean | true, false      | true    | Only show after passing the Add To Cart button |

> ms = milliseconds

## Preview

| Desktop      | Tablet       | Mobile       |
| ------------ | ------------ | ------------ |
| ![preview-1] | ![preview-2] | ![preview-3] |

[preview-1]: ./media/A-simple.jpg "Preview of Sticky Add To Cart on Desktop view"
[preview-2]: ./media/A-simple-tablet.jpg "Preview of Sticky Add To Cart on Tablet view"
[preview-3]: ./media/A-simple-mobile.jpg "Preview of Sticky Add To Cart on Mobile view"

> As stated in the [Configuration Options](#configuration-options) the desktop view is by default disabled.

## Notes

It's designed to work best with longer product pages, hence the default `mobile_only` option being set to `true`.

## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see the LICENSE.md file in the root of this repository for more
information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.3.11-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
