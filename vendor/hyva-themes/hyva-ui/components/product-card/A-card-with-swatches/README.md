# Hyvä UI - product-card.A - card with swatches

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä product list items into something new with this UI Component, that adds a new look and feel.

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Catalog/templates`
   * `Magento_Review/templates/helper/summary_short.phtml`
   * `web/tailwind/components/product-list.css`
2. Adjust the content and code to fit your own needs and save
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

## Preview

| Type      |              |
| --------- | ------------ |
| Grid view | ![preview-1] |
| List view | ![preview-2] |

[preview-1]: ./media/A-card-with-swatches-grid.jpg "Preview of product card on grid view"
[preview-2]: ./media/A-card-with-swatches-list.jpg "Preview of product card on list view"

> **Note:** The swatches displayed in the screenshots below make use of `Swatches A`, which can be installed separately.

## Notes

The `x-defer` tag is only supported with Hyva theme module version 1.3.7 or higher.

If you're using an older version of the Hyva theme module, this tag will be ignored.

For about the [`x-defer` Apline plugin](https://docs.hyva.io/hyva-themes/view-utilities/alpine-defer-plugin.html) see our docs.

---

A CSP-compliant version of the image is included, which removes the `hover:shadow-sm` and `object-contain` classes.  
To use the CSP version, replace the original image with the CSP image and rename it to `image`.

## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see the LICENSE.md file in the root of this repository for more
information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.3.11-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
[AlpineJS Supported Versions]: https://img.shields.io/badge/AlpineJS-3-8BC0D0?style=for-the-badge&logo=alpine.js "AlpineJS Supported Versions"
