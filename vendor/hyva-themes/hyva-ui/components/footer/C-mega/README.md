# Hyvä UI - footer.C - mega

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä Footer into something new with this UI Component, that adds a new look and feel.

## Usage - Template

1. Ensure you've followed the steps from Footer B UI Component (see [Requirements](#requirements) below)
2. Ensure you've installed Payments Icons in your project (see [Requirements](#requirements) below)
3. Copy or merge the following files/folders into your theme:
   * `Magento_Theme/layout/default.xml`
   * `Magento_Theme/templates/html/footer/cta.phtml`
   * `Magento_Theme/templates/html/footer/usps.phtml`
   * `Hyva_Theme/web/svg`
4. Adjust the content and code to fit your own needs and save
5. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

## Preview

| Desktop      | Tablet       | Mobile       |
| ------------ | ------------ | ------------ |
| ![preview-1] | ![preview-2] | ![preview-3] |

[preview-1]: ./media/C-mega.jpg "Preview of Footer on Desktop view"
[preview-2]: ./media/C-mega-tablet.jpg "Preview of Footer on Tablet view"
[preview-3]: ./media/C-mega-mobile.jpg "Preview of Footer on Mobile view"

## Requirements

### UI Component - Footer B

Please ensure you have completed the steps outlined in the Footer B UI Component documentation before proceeding with these instructions.

The Footer B UI Component documentation can be found in [`./components/footer/B-4-column-newsletter/README.md`](../B-4-column-newsletter/README.md).

### Payment Icons

We use our [Hyvä Payments Icons](https://docs.hyva.io/hyva-themes/view-utilities/hyva-svg-icon-modules/payment-icons.html) module, which you have to install separately.

Please, refer to the [installation instructions](https://docs.hyva.io/hyva-themes/view-utilities/hyva-svg-icon-modules/payment-icons.html#installation) on our documentation.

## Notes

There are also some custom icons present for the social icons,
for more information about how to use custom SVG icons,
see our [documentation](https://docs.hyva.io/hyva-themes/writing-code/working-with-view-models/svgicons.html).

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
