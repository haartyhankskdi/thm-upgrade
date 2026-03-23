# Hyvä UI - footer.B - 4 column newsletter

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä Footer into something new with this UI Component, that adds a new look and feel.

## Usage - Template

1. Ensure you've installed Hyvä Payments Icons in your project (see [Requirements](#requirements) below)
2. Copy or merge the following files/folders into your theme:
   * `Magento_Customer/templates`
   * `Magento_Customer/layout`
   * `Magento_Newsletter/templates`
   * `Magento_Sales/layout`
   * `Magento_Theme/templates`
   * `Magento_Theme/layout`
3. Adjust the content and code to fit your own needs and save
4. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Use CMS Static Blocks

Embrace the power of CMS Static Blocks to tailor the footer content to your specific needs.

Leverage the provided sample XML file `src/Magento_Theme/layout/default.xml` as a guide for seamlessly replacing static links with dynamic CMS content.

This approach enables you to effortlessly integrate your desired CMS blocks into the footer layout,
enhancing personalization and content management capabilities.

### Control Footer Content Display: Column vs. Collapse

The footer utilizes the `collapse` component to render footer menus within a grid layout.

This `collapse` behavior strategically hides the menus on mobile devices,
mirroring the familiar `details` and `collapse` functionality from HTML and accordions,
but tailored specifically for mobile viewing.

If you prefer to maintain menu visibility on mobile,
simply replace the corresponding menu template with the `column` component.

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Theme/layout/default.xml` file.

| Option Name | Type    | Available Values                         | Default | Description                                      |
| ----------- | ------- | ---------------------------------------- | ------- | ------------------------------------------------ |
| `title`     | string  | _leave empty for no title in the column_ |         | The title to show for the collapse or column     |
| `open`      | boolean | true, false                              | false   | Set the collapse to be open by default on mobile |

## Preview

| Desktop      | Mobile       |
| ------------ | ------------ |
| ![preview-1] | ![preview-2] |

[preview-1]: ./media/B-4-column-newsletter.jpg "Preview of Footer on Desktop view"
[preview-2]: ./media/B-4-column-newsletter-mobile.jpg "Preview of Footer on Mobile view"

## Requirements

### Payment icons

We use our [Hyvä Payments Icons](https://docs.hyva.io/hyva-themes/view-utilities/hyva-svg-icon-modules/payment-icons.html) module, which you have to install separately.

Please, refer to the [installation instructions](https://docs.hyva.io/hyva-themes/view-utilities/hyva-svg-icon-modules/payment-icons.html#installation) on our documentation.

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
