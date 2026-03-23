# Hyvä UI - mobile-menu.B - Tabs

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

This component reimagines the Hyvä Mobile Menu with a visual style inspired by the traditional Magento 2 Luma Menu Tabs.

Built upon the robust foundation of the Hyvä UI Component Mobile Menu A.

## Usage - Template

1. Ensure you've followed the steps from Mobile Menu A UI Component (see [Requirements](#requirements) below)
2. Copy or merge the following files/folders into your theme:
   * `Magento_Theme/templates/html/header/menu/mobile.phtml`
3. Adjust the content and code to fit your own needs and save
4. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Theme/layout/default.xml` file.

| Option Name             | Type    | Available Values | Default | Description                                        |
| ----------------------- | ------- | ---------------- | ------- | -------------------------------------------------- |
| `max_level`             | number  | _Number Range_   | 4       | Max Menu depth to show                             |
| `show_search`           | boolean | true, false      | true    | Show Search field                                  |
| `show_socials`          | boolean | true, false      | true    | Show Social Icons                                  |
| `show_settings_nav`     | boolean | true, false      | true    | Show Extra Nav with Language and Currency Settings |
| `additional_menu_items` | array   |                  |         | Extra Menu items to show                           |

<details><summary>Option <code>additional_menu_items</code> explained</summary>

This option uses a xml array of items, that need to include a `url` and `name` item as shown below, in the example:

```xml
<argument name="additional_menu_items" xsi:type="array">
    <item name="home" xsi:type="array">
        <item name="url" xsi:type="string">/</item>
        <item name="name" xsi:type="string" translate="true">Home</item>
    </item>
</argument>
```

Optionally you can also add the `external` item to the menu item.

</details>

## Preview

| Shop Tab     | Extra Tab    | Settings Tab |
| ------------ | ------------ | ------------ |
| ![preview-1] | ![preview-2] | ![preview-3] |

[preview-1]: ./media/B-tabs.jpg "Preview of the Mobile Menu B"
[preview-2]: ./media/B-tabs-extra.jpg "Preview of the Mobile Menu B Extra Menu"
[preview-3]: ./media/B-tabs-settings.jpg "Preview of the Mobile Menu B Settings tab"

## Requirements

### UI Component - Mobile Menu A

Please ensure you have completed the steps outlined in the Mobile Menu A UI Component documentation before proceeding with these instructions.

The Mobile Menu A UI Component documentation can be found in [`./components/menu-mobile/A-scroll/README.md`](../A-scroll/README.md).

## Notes

This UI component supports the addition of custom widgets within the "Mobile Menu Footer" location.

The Settings Accordion utilizes the HTML details element with the name attribute for enhanced accessibility.
This modern approach is supported in all major browsers.
For older browsers, a standard collapse mechanism will be used as a fallback.

---

For optimal integration and visual consistency, it is recommended to use this component in conjunction with the Hyvä UI Headers.

When using this component with the Default Theme Header,
ensure that the following CSS classes `order-2 sm:order-1 lg:order-2`  are applied to the `Magento_Theme/templates/html/header/menu/mobile.phtml` wrapper.

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
