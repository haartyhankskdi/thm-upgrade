# Hyvä UI - Mini Cart - A Classic

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä Mini Cart into something new with this UI Component, that adds a new look and feel and offers qty styles for changing the qty directly from the minicart.

## Usage - Template

1. Ensure you've installed `x-htmldialog` in your project (see [Requirements](#requirements) below)
2. Copy or merge the following files/folders into your theme:
   * `Magento_Theme/templates/html/cart`
   * `Magento_Theme/layout/default.xml`
   * `web/tailwind/utilities/mask-overflow.css`
3. Make sure to import the `mask-overflow.css` in your `tailwind-source.css` file
4. Adjust the content and code to fit your own needs and save
5. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Theme/layout/default.xml` file.

| Option Name  | Type    | Available Values                         | Default | Description                                    |
| ------------ | ------- | ---------------------------------------- | ------- | ---------------------------------------------- |
| `show_sku`   | boolean | true, false                              | true    | Enable to show sku in product options          |
| `qty_style ` | string  | `text`, `input`, `select`, `incrementor` | `input` | How the qty style is displayed in the minicart |

<details><summary>Option <code>qty_style</code> explained</summary>

The `qty_style` property provides various options for displaying and manipulating product quantities,
each with distinct styles and behaviors:

- `text`: This is the most basic option, simply displaying the quantity value as text before the product title.
- `input`: This adds a dedicated quantity input box.
- `select`: This also includes an input box,
  progressively enhanced with the [datalist] element for autocomplete suggestions based on typed values.
  Additionally, it provides a dropdown menu for selecting pre-defined quantities.
- `incrementor`: This utilizes a plus/minus button interface for quantity adjustment.

[datalist]: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/datalist

</details>

## Preview

| Type                              |              |
| --------------------------------- | ------------ |
| Empty                             | ![preview-1] |
| Default (qtybox)                  | ![preview-2] |
| Qty in product title (Text Style) | ![preview-3] |
| Qty as selectbox                  | ![preview-4] |
| Qty with incrementor              | ![preview-5] |

[preview-1]: ./media/A-classic-empty.jpg "Preview of the mincart without items"
[preview-2]: ./media/A-classic.jpg "Preview of the mincart with qtybox"
[preview-3]: ./media/A-classic-qty-text.jpg "Preview of the mincart without qtybox"
[preview-4]: ./media/A-classic-qty-select.jpg "Preview of the mincart with qtybox as selectbox"
[preview-5]: ./media/A-classic-qty-incrementor.jpg "Preview of the mincart with qtybox with incrementor"

## Requirements

### AlpineJS `x-htmldialog`

To enable this component, the Alpine.js [x-htmldialog] plugin is necessary. Follow these steps for integration:

1.  From the `alpine-htmldialog` plugin directory, copy `Magento_Theme/templates/page/js/plugins/htmldialog.phtml` into your theme or module's template folder.
2.  Similarly, copy `Magento_Theme/layout/default.xml` from the `alpine-htmldialog` plugin directory into your theme or module's layout folder.

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
