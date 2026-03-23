# Hyvä UI - header.C - stacked

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä Header into something new with this UI Component, that adds a new look and feel.

## Usage - Template

1. Ensure you've added the UI Component search-form.A in your project (see [Requirements](#requirements) below)
2. Copy or merge the following files/folders into your theme:
   * `Magento_Theme/templates`
   * `Magento_Theme/layout/default.xml`
   * `Magento_Catalog/templates/product/compare/link.phtml`
   * `Magento_Catalog/layout/default.xml`
   * `Magento_Customer/templates/header/customer-menu.phtml`
   * `Magento_Wishlist/templates/header/wishlist.phtml`
   * `Magento_Wishlist/layout/default.xml`
   * `Magento_Store/templates/header/languages.phtml`
   * `Magento_Store/layout/default.xml`
3. Adjust the content and code to fit your own needs and save
4. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Theme/layout/default.xml` file.

| Option Name     | Type    | Available Values | Default | Description          |
| --------------- | ------- | ---------------- | ------- | -------------------- |
| `show_compare`  | boolean | true, false      | true    | Show compare button  |
| `show_wishlist` | boolean | true, false      | true    | Show wishlist button |
| `show_language` | boolean | true, false      | true    | Show language select |

## Preview

| Desktop      | Tablet       | Mobile       |
| ------------ | ------------ | ------------ |
| ![preview-1] | ![preview-2] | ![preview-3] |

[preview-1]: ./media/C-stacked.jpg "Preview of Header on Desktop view"
[preview-2]: ./media/C-stacked-tablet.jpg "Preview of Header on Tablet view"
[preview-3]: ./media/C-stacked-mobile.jpg "Preview of Header on Mobile view"

## Requirements

### UI Component: search-form.A - header

This component works with the UI Component search-form.A and needs to be added when using this component.

In order to add the search-form.A, follow the following step explained in the UI Component search-form.A.

## Notes

In the preview, you can see this header paired with Desktop Menu C. However, this header is also compatible with other UI menus.
Please refer to the documentation for each specific menu to learn how it works with this header.

## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see the LICENSE.md file in the root of this repository for more
information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.3.11.2-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
[AlpineJS Supported Versions]: https://img.shields.io/badge/AlpineJS-3-8BC0D0?style=for-the-badge&logo=alpine.js "AlpineJS Supported Versions"
