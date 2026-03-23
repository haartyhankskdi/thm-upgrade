# Hyvä UI - product-data.B - accorditabs

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä product section into something new with this UI Component, that adds a new look and feel.

## Usage - Template

1. Ensure you've installed `x-collapse` in your project (see [Requirements](#requirements) below)
2. Copy or merge the following files/folders into your theme:
   * `Magento_Catalog/templates`
   * `Magento_Catalog/layout`
   * `Magento_Review/templates`
   * `Magento_Review/layout`
3. Adjust the content and code to fit your own needs and save
4. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Catalog/layout/catalog_product_view.xml` file.

| Option Name | Type    | Available Values | Default | Description                                                            |
| ----------- | ------- | ---------------- | ------- | ---------------------------------------------------------------------- |
| `divider`   | boolean | true, false      | true    | Controls whether a divider should be displayed between accordion items |

## Preview

| Desktop      | Mobile       |
| ------------ | ------------ |
| ![preview-1] | ![preview-2] |

[preview-1]: ./media/B-accorditabs.jpg "Preview of Accordion-Tabs on Desktop view"
[preview-2]: ./media/B-accorditabs-mobile.jpg "Preview of Accordion-Tabs on Desktop view"

## Requirements

### AlpineJS `x-collapse`

To enable this component, the Alpine.js [x-collapse] plugin is necessary.

Follow these steps for integration:

1.  From the `alpine-collapse` plugin directory, copy `Magento_Theme/templates/page/js/plugins/collapse.phtml` into your theme or module's template folder.
2.  Similarly, copy `Magento_Theme/layout/default.xml` from the `alpine-collapse` plugin directory into your theme or module's layout folder.

## Notes

The `x-defer` tag is only supported with Hyva theme module version 1.3.7 or higher.

If you're using an older version of the Hyva theme module, this tag will be ignored.

For about the [`x-defer` Apline plugin](https://docs.hyva.io/hyva-themes/view-utilities/alpine-defer-plugin.html) see our docs.

## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see the LICENSE.md file in the root of this repository for more
information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"
[x-collapse]: https://alpinejs.dev/plugins/collapse

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.3.11-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
[AlpineJS Supported Versions]: https://img.shields.io/badge/AlpineJS-3-8BC0D0?style=for-the-badge&logo=alpine.js "AlpineJS Supported Versions"
