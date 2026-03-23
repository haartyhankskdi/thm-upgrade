# Hyvä UI - slider.C - Product

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Our implementation of a Product Slider, powered by our new Snap Slider for a super light CSS powered CSS slider.

## Usage - Template

1. Ensure you've installed `x-snap-slider` in your project (see [Requirements](#requirements) below)
2. Copy or merge the following files/folders into your theme:
   * `Magento_Catalog/templates/product/slider/product-slider.phtml`
3. Adjust the content and code to fit your own needs and save
4. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

> These settings are identical to those in the default theme and are therefore not repeated here for brevity.

## Preview

| Type    | Desktop      | Mobile       |
| ------- | ------------ | ------------ |
| Default | ![preview-1] | ![preview-2] |

[preview-1]: ./media/A-basic.jpg "Preview of Slider in Desktop View"
[preview-2]: ./media/A-basic-mobile.jpg "Preview of Slider in Mobile View"

## Requirements

### AlpineJS `x-snap-slider`

To enable this component, the Alpine.js `x-snap-slider` plugin is necessary. Follow these steps for integration:

1.  From the `alpine-snap-slider` plugin directory, copy `Magento_Theme/templates/page/js/plugins/snap-slider.phtml` into your theme or module's template folder.
2.  Similarly, copy `Magento_Theme/layout/default.xml` from the `alpine-snap-slider` plugin directory into your theme or module's layout folder.

## Notes

The `x-defer` tag is only supported with Hyva theme module version 1.3.7 or higher.

If you're using an older version of the Hyva theme module, this tag will be ignored.

For about the [`x-defer` Apline plugin](https://docs.hyva.io/hyva-themes/view-utilities/alpine-defer-plugin.html) see our docs.

---

If you are using a Hyva theme module version older than 1.3.13 with the CSP Theme,
an empty `x-data` attribute will generate an "undefined Alpine component" warning.

This warning does not impact the functionality of the UI component itself.

To eliminate this warning, either provide a valid Alpine component within the `x-data` attribute
or upgrade your Hyva theme module to version 1.3.13 or higher.

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
