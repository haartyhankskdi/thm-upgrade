# Hyvä UI - categoryfilter.A - standard

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

This component replaces the Category Layered Navigation (filter) and Swatches for a more stylized version, including a Price Slider without the need for an extension.

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Catalog/`
   * `Magento_LayeredNavigation/`
   * `web/tailwind/theme/components/style/forms-range.css`
2. Make sure to import the `forms-range.css` in your `tailwind-source.css` file
3. Adjust the content and code to fit your own needs and save
4. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_LayeredNavigation/layout/catalog_category_view_type_layered.xml`
and `src/Magento_LayeredNavigation/layout/catalogsearch_result_index.xml` file.

| Option Name                | Type    | Available Values | Default          | Description                                                    |
| -------------------------- | ------- | ---------------- | ---------------- | -------------------------------------------------------------- |
| `active_open`              | boolean | true, false      | true             | If the active filters are open by default                      |
| `active_style`             | string  | `list`, `chips`  | `chips`          | Display the active filter as a list or as chips                |
| `show_input`               | boolean | true, false      | true             | Show a input with each option _#1_                             |
| `show_price_slider`        | boolean | true, false      | true             | Show a price slider instead of a list of options               |
| `max_price_fallback_value` | number  | _Number Range_   | 1000             | Set the fallback max price value if no filter options are left |
| `open_by_ids`              | array   |                  | [`cat`, `price`] | Which Filter options are open by default                       |

> #1: `show_input` is also set in `src/Magento_Catalog/layout/catalog_category_view_type_default.xml`, so make sure to also update this file

<details><summary>Option <code>open_by_ids</code> explained</summary>

To include new IDs in the default open filters, simply add the item name and its corresponding value.

Ensure that the value matches the filter ID. To view the filter ID, refer to the filter's Attribute Code.

For example:

```xml
<argument name="open_by_ids" xsi:type="array">
    <item name="color" xsi:type="string">color</item>
</argument>
```

</details>

## Preview

### Desktop

| Category Type | Desktop         | Desktop with input | Mobile          |
| ------------- | --------------- | ------------------ | --------------- |
| Default       | ![screenshot-1] | ![screenshot-2]    | ![screenshot-3] |
| No Anchor     | ![screenshot-4] | ![screenshot-5]    |                 |

### Active style

| Default         | with chip       |
| --------------- | --------------- |
| ![screenshot-6] | ![screenshot-7] |

> **Note:** The swatches displayed in the screenshots below make use of `Swatches A`, which can be installed separately.

### Price Slider

![screenshot-8]

[screenshot-1]: ./media/A-standard.jpg "Filters on desktop"
[screenshot-2]: ./media/A-standard-with-input.jpg "Filters on desktop with input style"
[screenshot-3]: ./media/A-standard-mobile.jpg "Filters on mobile"
[screenshot-4]: ./media/A-standard-no-anchor.jpg "No Anchor Category Filters on desktop"
[screenshot-5]: ./media/A-standard-no-anchor-with-input.jpg "No Anchor Category Filters on desktop with input style"
[screenshot-6]: ./media/A-standard-has-active.jpg "Active Filters on desktop"
[screenshot-7]: ./media/A-standard-has-active-as-chips.jpg "Active Filters on desktop with chips style"
[screenshot-8]: ./media/A-standard-price-slider.jpg "Filters with Price Slider"

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

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.3.11-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
[AlpineJS Supported Versions]: https://img.shields.io/badge/AlpineJS-3-8BC0D0?style=for-the-badge&logo=alpine.js "AlpineJS Supported Versions"
