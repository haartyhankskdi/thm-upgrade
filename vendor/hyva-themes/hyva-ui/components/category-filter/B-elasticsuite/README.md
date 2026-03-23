# Hyvä UI - categoryfilter.B - Smile Elasticsuite

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

This component replaces the Hyvä Smile Elasticsuite Layered Navigation (filter) for a more stylized version.

It extends upon the Hyvä UI Category Filter - A Standard component and can be seamlessly integrated with it.

## Usage - Template

1. Ensure you've installed [Hyvä Smile Elasticsuite] module in your project (see [Requirements](#requirements) below)
2. Copy or merge the following files/folders into your theme:
   * `Hyva_SmileElasticsuite/templates`
   * `Hyva_SmileElasticsuite/layout`
   * `Magento_LayeredNavigation/templates/layer/state.phtml`
   * `web/tailwind/theme/components/style/forms-range.css`
3. Make sure to import the `forms-range.css` in your `tailwind-source.css` file
4. Adjust the content and code to fit your own needs and save
5. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

> This uses the same foundation as used in the **Hyvä UI Category Filter - A Standard** and can be added on top of that UI component,
> this way you only need to add the code from `Hyva_SmileElasticsuite` folder

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Hyva_SmileElasticsuite/layout/hyva_catalog_category_view_type_layered.xml`
and `src/Hyva_SmileElasticsuite/layout/catalogsearch_result_index.xml` file.

| Option Name    | Type    | Available Values | Default | Description                                     |
| -------------- | ------- | ---------------- | ------- | ----------------------------------------------- |
| `active_open`  | boolean | true, false      | true    | If the active filters are open by default       |
| `active_style` | string  | `list`, `chips`  | `chips` | Display the active filter as a list or as chips |

## Preview

| Mobile       | Desktop      |
| ------------ | ------------ |
| ![preview-1] | ![preview-2] |

> **Note:** The swatches displayed in the screenshots below make use of `Swatches A`, which can be installed separately.

### Active style

| Default (Chips) | List style   |
| --------------- | ------------ |
| ![preview-3]    | ![preview-4] |

### Price Slider

![preview-5]

[preview-1]: ./media/B-elasticsuite-mobile.jpg "Filters on mobile"
[preview-2]: ./media/B-elasticsuite.jpg "Filters on desktop"
[preview-3]: ./media/B-elasticsuite-has-active-as-chips.jpg "Active Filters on desktop with chips style"
[preview-4]: ./media/B-elasticsuite-has-active.jpg "Active Filters on desktop"
[preview-5]: ./media/B-elasticsuite-price-slider.jpg "Filters with Price Slider"

## Requirements

### Smile Elasticsuite

Make sure you have installed [Smile Elasticsuite] and the [Hyvä Smile Elasticsuite] module before adding this UI component.

## Notes

The `x-defer` tag is only supported with Hyva theme module version 1.3.7 or higher.

If you're using an older version of the Hyva theme module, this tag will be ignored.

For about the [`x-defer` Apline plugin](https://docs.hyva.io/hyva-themes/view-utilities/alpine-defer-plugin.html) see our docs.

---

While the Smile Elasticsuite version shares similarities with the **Hyvä UI Category Filter - A Standard**, it differs in its configuration approach.

In the Smile Elasticsuite version, most options are set through Elasticsuite rather than the XML options provided in the standard counterpart.

Additionally, some configuration options are not configurable due to the inherent structure of Elasticsuite.

---

Like the Hyvä Compatibility module, this UI component is currently incompatible with Content Security Policy (CSP).

CSP support will be added when the Compatibility module achieves CSP compliance

## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see the LICENSE.md file in the root of this repository for more
information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"
[Smile Elasticsuite]: https://github.com/Smile-SA/elasticsuite
[Hyvä Smile Elasticsuite]: https://gitlab.hyva.io/hyva-themes/hyva-compat/magento2-smile-elasticsuite/

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.3-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
[AlpineJS Supported Versions]: https://img.shields.io/badge/AlpineJS-3-8BC0D0?style=for-the-badge&logo=alpine.js "AlpineJS Supported Versions"
