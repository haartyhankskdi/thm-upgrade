# Hyvä UI - product reviews.B - minimal

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä Reviews into something new with this UI Component, that adds a new look and feel.

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Reviews/templates`
   * `Magento_Reviews/layout`
2. Adjust the content and code to fit your own needs and save
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Reviews/layout/catalog_product_view.xml` file.

| Option Name        | Type    | Available Values | Default | Description                                |
| ------------------ | ------- | ---------------- | ------- | ------------------------------------------ |
| `show_avatar`      | boolean | true, false      | true    | Show avatar of reviewer next to the review |
| `rating_star_size` | number  | _Number Range_   | 20      | Controls review star size                  |

## Preview

| Type        |              |
| ----------- | ------------ |
| Default     | ![preview-1] |
| With Avatar | ![preview-2] |

[preview-1]: ./media/B-minimal.jpg "Preview of Reviews"
[preview-2]: ./media/B-minimal-with-avatar.jpg "Preview of Reviews with Avatar"

## Notes

The `show_avatar` option requires the Hyvä Theme Module versions never than 1.3.3,
if this not the case the option is ignored.

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
