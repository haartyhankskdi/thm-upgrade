# Hyvä UI - pagination.A - clean

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä Pagination into something new with this UI Component, that adds a new look and feel.

## Usage - Template

1. Ensure you've added Buttons A in your project (see [Requirements](#requirements) below)
2. Copy or merge the following files/folders into your theme:
   * `Magento_Theme/templates/html/pager.phtml`
   * `Magento_Catalog/templates/product/list`
3. Adjust the content and code to fit your own needs and save
4. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

## Preview

| Type     | Desktop      | Mobile       |
| -------- | ------------ | ------------ |
| Default  | ![preview-1] | ![preview-2] |
| Ellipsis | ![preview-3] | ![preview-2] |

[preview-1]: ./media/A-clean.jpg "Pagination on Desktop view"
[preview-2]: ./media/A-clean-mobile.jpg "Pagination on Mobile view"
[preview-3]: ./media/A-clean-with-jumps.jpg "Pagination on Desktop view with Ellipsis"

## Requirements

### Buttons A

This component works without the UI components Buttons A,
but for the experience you should include the Buttons A UI Component.

The Buttons A UI Component documentation can be found in [`./components/buttons/A-basic/README.md`](../../buttons/A-basic/README.md).

## Notes

The preview displays the catalog version of the pagination component with additional toolbar options.

This pagination style is also available on other pages, such as the Account Page and Cart, though without the toolbar options.

---

This pagination has been optimized for responsiveness across multiple screen sizes,
making it easier to navigate through more pager items or skip pages efficiently.

To configure the pagination settings, navigate to: `Content → Design → Configuration → <YOUR_THEME> → Pagination`.

Here, you can adjust the following options:

- **Pagination Frame:** The number of pagination items to display.
- **Pagination Frame Skip:** The number of items to skip before showing the ellipsis.

We recommend using moderate values for these settings to avoid overlapping pagination elements on desktop screens.

On mobile, the UI component styles automatically hide items that don’t fit within the container’s width, ensuring that the visible items are appropriately sized.

In the preview, we used the following values:

| Option                | Default | Ellipsis |
| --------------------- | :-----: | :------: |
| Pagination Frame      |    5    |    3     |
| Pagination Frame Skip |    0    |    2     |

## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see the LICENSE.md file in the root of this repository for more
information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.3-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
[AlpineJS Supported Versions]: https://img.shields.io/badge/AlpineJS-3-8BC0D0?style=for-the-badge&logo=alpine.js "AlpineJS Supported Versions"
