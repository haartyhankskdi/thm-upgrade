# Hyvä UI - product-data.A - specs

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä product section specs into something new with this UI Component, that adds a new look and feel.

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Catalog/templates`
   * `Magento_Catalog/layout/catalog_product_view.xml`
2. Adjust the content and code to fit your own needs and save
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Catalog/layout/catalog_product_view.xml` file.

| Option Name    | Type   | Available Values | Default | Description                          |
| -------------- | ------ | ---------------- | ------- | ------------------------------------ |
| `layout_style` | string | `list`, `row`    |         | Specifies the layout to utilize _#1_ |

> #1: if no options is used the `row` is used on desktop and `list` on mobile

## Preview

| Row          | List         |
| ------------ | ------------ |
| ![preview-1] | ![preview-2] |

[preview-1]: ./media/A-specs.jpg "Preview of specs in row view"
[preview-2]: ./media/A-specs-list.jpg "Preview of specs in list view"

## Notes

The `hypends-auto` has no effect in Hyva versions shipping with Tailwind v3.2,
but this can be enabled by upgrading [Tailwind to v3.3](https://github.com/tailwindlabs/tailwindcss/releases/tag/v3.3.0), using `npm install tailwindcss@3.3` in your `web/tailwind` theme folder.

## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see the LICENSE.md file in the root of this repository for more
information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.2,_1.3-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
[AlpineJS Supported Versions]: https://img.shields.io/badge/AlpineJS-3-8BC0D0?style=for-the-badge&logo=alpine.js "AlpineJS Supported Versions"
