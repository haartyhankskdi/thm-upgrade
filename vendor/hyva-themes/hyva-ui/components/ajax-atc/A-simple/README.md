# Hyvä UI - ajax-atc.A - simple

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Streamline your shopping experience with our Ajax Add To Cart UI Component.

Seamlessly add products to your cart without page reloads, and see a quick modal confirming your selection.

## Usage - Template

1. Ensure you've installed `x-htmldialog` in your project (see [Requirements](#requirements) below)
2. Copy or merge the following files/folders into your theme:
   * `Magento_Theme/templates`
   * `Magento_Theme/layout`
3. Adjust the content and code to fit your own needs and save
4. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Theme/layout/default.xml` file.

| Option Name          | Type    | Available Values           | Default                                            | Description                                                 |
| -------------------- | ------- | -------------------------- | -------------------------------------------------- | ----------------------------------------------------------- |
| `delay`              | number  | _Number Range_             | 500                                                | Extra delay in milliseconds added to the loader             |
| `show_sku`           | boolean | true, false                | true                                               | Enable to sku in product options                            |
| `display_on_success` | string  | `modal`, `minicart`, false | `modal`                                            | What to show on success                                     |
| `form_selectors`     | string  |                            | `#product_addtocart_form, .product_addtocart_form` | Configure the form selectors that will trigger the Ajax ATC |

## Preview

| Type  | Desktop      | Tablet       | Mobile       |
| ----- | ------------ | ------------ | ------------ |
| Modal | ![preview-1] | ![preview-2] | ![preview-3] |

[preview-1]: ./media/A-simple.jpg "Preview of the Modal on Desktop view"
[preview-2]: ./media/A-simple-tablet.jpg "Preview of the Modal on Tablet view"
[preview-3]: ./media/A-simple-mobile.jpg "Preview of the Modal on Mobile view"

> The `false` and `minicart` use Hyvä Default Theme for the style

## Requirements

### AlpineJS `x-htmldialog`

To enable this component, the Alpine.js [x-htmldialog] plugin is necessary. Follow these steps for integration:

1.  From the `alpine-htmldialog` plugin directory, copy `Magento_Theme/templates/page/js/plugins/htmldialog.phtml` into your theme or module's template folder.
2.  Similarly, copy `Magento_Theme/layout/default.xml` from the `alpine-htmldialog` plugin directory into your theme or module's layout folder.

## Notes

The Modal option will fallback to the false option for Grouped and Bundled Products, and will just show the default message.

### Hiding the Default Success Message

Directly hiding the default success message isn't currently an available option.

However, for a better user experience,
we recommend setting a default timeout for success messages.

This can be configured in `Stores → Configuration → Hyvä Themes → General → Success Message Default Timeout`.

This approach allows success messages to be displayed briefly before automatically disappearing,
providing feedback to the user without being overly intrusive.

### Using Ajax with Synchronize widget products with backend storage

AJAX-based UI components, including this one, are incompatible with the Magento 2 setting `Synchronize widget products with backend storage`.

To ensure proper functionality, please disable this setting before using this UI component.

The setting can be found in `Stores → Configuration → Catalog → Catalog`.

### Using with Forms that Include File Upload Custom Options

The Ajax Add to Cart (ATC) functionality supports custom options,
with one exception: multipart forms (forms that include file upload options).

In cases where a file upload is involved, the form will automatically revert to a standard form submission,
bypassing the Ajax behavior.

## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see the LICENSE.md file in the root of this repository for more
information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"
[x-htmldialog]: https://fylgja.dev/library/extensions/alpinejs-dailog/

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.3.11-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
[AlpineJS Supported Versions]: https://img.shields.io/badge/AlpineJS-3-8BC0D0?style=for-the-badge&logo=alpine.js "AlpineJS Supported Versions"
