# Hyvä UI - menu.D - Shop dropdown

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

This is a desktop Mega Menu with a Shop Menu Button and multi layered drilldown menu's inside a menu panel.

The Desktop Mega Menu D allows you to create a menu layout with a Shop Menu Button, multi-level drilldown menus, and static content block integration.

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Theme/templates`
   * `Magento_Theme/layout/default.xml`
2. Adjust the content and code to fit your own needs and save
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Theme/layout/default.xml` file.

| Option Name | Type   | Available Values | Default | Description            |
| ----------- | ------ | ---------------- | ------- | ---------------------- |
| `max_level` | number | _Number Range_   | 4       | Max Menu depth to show |

### Display CMS Content in Top Level Menu Panel

The menu can display custom content from CMS blocks for your top level.

Here's how to add CMS content to your menu:

1. **Create CMS Blocks:** Build your desired content using CMS blocks.
2. **Assign Block Identifiers:** Use the identifier format `desktop-menu-panel`.

## Preview

| Collapsed    | Open         | with CMS Block |
| ------------ | ------------ | -------------- |
| ![preview-1] | ![preview-2] | ![preview-3]   |

[preview-1]: ./media/D-shop-dropdown.jpg "Preview of the Menu D"
[preview-2]: ./media/D-shop-dropdown-open.jpg "Preview of the Menu D open"
[preview-3]: ./media/D-shop-dropdown-open-with-block.jpg "Preview of the Menu D open with a CMS block"

## Notes

It's recommended to pair Menu.D with Header.B for the best results (as shown in the previews).

However, Menu.D is also flexible and can be used with other UI Headers, including the Default Theme Header.

For the Default Theme Header specifically,
you'll need to apply the classes `order-2 sm:order-1 lg:order-2` to the top element of your Menu.D to maintain the intended layout.

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
