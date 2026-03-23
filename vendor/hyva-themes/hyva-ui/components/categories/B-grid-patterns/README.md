# Hyvä UI - categories.B - grid - patterns

[![cms-support]](https://docs.hyva.io/hyva-ui-library/faqs/cms-components.html)
[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![Figma]](https://www.figma.com/@hyva)

Enhance your pages with our UI component, designed to simplify the creation of stunning and responsive layouts.

## Usage - CMS

1. Ensure you've installed CMS Tailwind JIT module in your project (see [Requirements](#requirements) below)
2. Copy the contents from `cms-content` into your CMS page or Block
3. Adjust the content and code to fit your own needs and save
4. Refresh the cache

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Cms/templates/elements/categories-b.phtml`
2. Adjust the content and code to fit your own needs and save
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

> There's an example how to insert the `categories-a.phtml` into your homepage in `Magento_Cms/layout/cms_index_index.xml`

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Theme/layout/cms_index_index.xml` file.

| Option Name  | Type    | Available Values | Default | Description                                               |
| ------------ | ------- | ---------------- | ------- | --------------------------------------------------------- |
| `use_slider` | boolean | true, false      | true    | Controls whether the mobile view should use an CSS Slider |

## Preview

| Grid         | Slider (Mobile) |
| ------------ | --------------- |
| ![preview-1] | ![preview-2]    |

[preview-1]: ./media/B-grid-patterns.jpg "Preview of Grid of Images in Desktop View"
[preview-2]: ./media/B-grid-patterns-mobile.jpg "Preview of Grid of Images as Slider in Mobile View"

## Requirements

### [CMS Tailwind JIT]

This component works with the [CMS Tailwind JIT] module to seamlessly integrate Tailwind CSS classes into your CMS content.

This module enables direct pasting of `cms-content` contents into CMS pages or blocks,
automatically generating the corresponding Tailwind CSS styles.

For installation instructions, refer to the [CMS Tailwind JIT] module's documentation.

## Notes

While it would work to keep the custom css classes above the html, it is best practice to move that to a css file.

The svg background patterns are from [Heropatterns](https://heropatterns.com) and optimizes with [svgomg](https://jakearchibald.github.io/svgomg/).

## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see the LICENSE.md file in the root of this repository for more
information.

[cms-support]: https://img.shields.io/badge/cms_support-ffc803?style=for-the-badge
[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"
[CMS Tailwind JIT]: https://docs.hyva.io/hyva-themes/cms/using-tailwind-classes-in-cms-content.html

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.2,_1.3-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
