# Hyvä UI - <UI_NAME> - <UI_NAME_VARIANT>

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

<!-- Intro with a short description of what the UI component replaces or adds to Hyvä -->

<!--
Sample New Block / CMS:
Enhance your pages with our UI component, designed to simplify the creation of stunning and responsive layouts.
---
Sample Existing Block / Replace:
Transform the Hyvä <BLOCK_NAME> into something new with this UI Component, that adds a new look and feel.
-->

## Usage - CMS

1. Ensure you've installed CMS Tailwind JIT module in your project (see [Requirements](#requirements) below)
2. Copy the contents from `cms-content` into your CMS page or Block
3. Adjust the content and code to fit your own needs and save
4. Refresh the cache

## Usage - Template

1. Ensure you've installed `x-htmldialog` in your project (see [Requirements](#requirements) below)
2. Copy or merge the following files/folders into your theme:
   * `Magento_Theme/templates`
   * `Magento_Theme/layout`
   * `web/tailwind/components/button.css`
   * `web/tailwind/components/new.css`
3. Make sure to import the `new.css` in your `tailwind-source.css` file
4. Adjust the content and code to fit your own needs and save
5. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Theme/layout/default.xml` file.

| Option Name      | Type    | Available Values   | Default   | Description                             |
| ---------------- | ------- | ------------------ | --------- | --------------------------------------- |
| `child_template` | string  | _Path to template_ |           | Specifies the child template to utilize |
| `title`          | string  |                    | `Details` |                                         |
| `divider`        | boolean | true, false        | true      |                                         |
| `delay`          | number  | _Number Range_     | 500       |                                         |

<details><summary>Option <code>`child_template`</code> explained</summary>

You can switch between `collapse` and native HTML `details` elements by providing the `child_template`:
- `Magento_Theme::elements/accordion/item-collapse.phtml`
- `Magento_Theme::elements/accordion/item-details.phtml`

The HTML Details element offers the same functionality as the Collapse, but with the benefit that the HTML Details element works even if there is no Javascript loaded.

The only downside (at the moment) is that the HTML Details element closes with no animation.

</details>

<!--
    Alternative version for Configuration Options set in the view.xml,
    mostly used for the gallery
-->
---

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI Component,
utilize the provided options below, with the default value set:

```xml
<var name="gallery">
    <var name="navdir">horizontal</var> <!-- Direction of the thumbnails (horizontal/vertical) -->
    <var name="navarrows">true</var> <!-- Turn on/off the thumbnail arrows (true/false) -->
    <!-- Hyva Only options -->
    <var name="navoverflow">false</var> <!-- Turn on/off overflow style (true/false) -->
</var>
```

To add any of these options, ensure that you only edit the section in your `etc/view.xml` under gallery:

```xml
<view xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/view.xsd">
    <vars module="Magento_Catalog">
        <var name="gallery">
            <!-- Add the options here -->
        </var>
    </vars>
</view>
```

## Preview

| Type    | Desktop      | Mobile       |
| ------- | ------------ | ------------ |
| Default | ![preview-1] | ![preview-2] |
| Variant | ![preview-3] | ![preview-4] |

[preview-1]: ./media/A-sample.jpg "Description"
[preview-2]: ./media/A-sample-mobile.jpg "Description"
[preview-3]: ./media/A-sample-variant.jpg "Description"
[preview-4]: ./media/A-sample-variant-mobile.jpg "Description"

## Requirements

### [CMS Tailwind JIT]

This component works with the [CMS Tailwind JIT] module to seamlessly integrate Tailwind CSS classes into your CMS content.

This module enables direct pasting of `cms-content` contents into CMS pages or blocks,
automatically generating the corresponding Tailwind CSS styles.

For installation instructions, refer to the [CMS Tailwind JIT] module's documentation.

### AlpineJS `x-htmldialog`

To enable this component, the Alpine.js [x-htmldialog] plugin is necessary. Follow these steps for integration:

1.  From the `alpine-htmldialog` plugin directory, copy `Magento_Theme/templates/page/js/plugins/htmldialog.phtml` into your theme or module's template folder.
2.  Similarly, copy `Magento_Theme/layout/default.xml` from the `alpine-htmldialog` plugin directory into your theme or module's layout folder.

## Notes

There is no container around this element, assuming your theme already has some kind of container.

The font-family is not altered, unlike the font-sizes and colors. You can change those to fit your design.

## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see the LICENSE.md file in the root of this repository for more
information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"
[CMS Tailwind JIT]: https://docs.hyva.io/hyva-themes/cms/using-tailwind-classes-in-cms-content.html
[x-collapse]: https://alpinejs.dev/plugins/collapse

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.3.11-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
[AlpineJS Supported Versions]: https://img.shields.io/badge/AlpineJS-3-8BC0D0?style=for-the-badge&logo=alpine.js "AlpineJS Supported Versions"
