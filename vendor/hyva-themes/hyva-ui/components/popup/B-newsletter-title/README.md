# Hyvä UI - popup.B - newsletter title

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

This example uses a newsletter template file, added in the footer by xml, to render a popup with the subscription form.

The popup is shown on each page load, so configure to you own needs.

## Usage - Template

1. Ensure you've installed `x-htmldialog` in your project (see [Requirements](#requirements) below)
2. Copy or merge the following files/folders into your theme:
   * `Magento_Newsletter/templates/modal.phtml`
   * `Magento_Newsletter/layout/default.xml`
3. Adjust the content and code to fit your own needs and save
4. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

## Preview

![Preview of Newsletter](./media/B-newsletter-title.jpg)

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
