# Hyvä UI - scroll-to-top.A - simple

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

The Scroll To Top offers a user-friendly solution for navigating to the top of your web page.

Ideal for lengthy content sections,
it promotes a seamless user experience by eliminating the need for manual scrolling.

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Theme/templates`
   * `Magento_Theme/layout`
2. Adjust the content and code to fit your own needs and save
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Theme/layout/default.xml` file.

| Option Name           | Type    | Available Values         | Default | Description                                    |
| --------------------- | ------- | ------------------------ | ------- | ---------------------------------------------- |
| `position`            | string  | `start`, `center`, `end` | `end`   | Inline position of the button                  |
| `visible_offset`      | number  | _Number Range_           | 200     | Offset before showing the button               |
| `visible_only_to_top` | boolean | true, false              | true    | Only show the button when scrolling to the top |
| `hide_on_inactivity`  | number  | _Number Range_           | 4000    | Hide the button after ms of inactivity         |

> ms = milliseconds

## Preview

| Type  | Desktop      | Mobile       |
| ----- | ------------ | ------------ |
| Modal | ![preview-1] | ![preview-2] |

[preview-1]: ./media/A-simple.jpg "Preview of the Scroll To Top Button on Desktop view"
[preview-2]: ./media/A-simple-mobile.jpg "Preview of the Scroll To Top Button on Mobile view"

## Notes

Scroll To Top and Sticky Header can be effective navigation aids,
but using both simultaneously can sometimes lead to redundancy or confusion.

Consider carefully which option, or a combination of both, best suits your specific design and user needs.

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
