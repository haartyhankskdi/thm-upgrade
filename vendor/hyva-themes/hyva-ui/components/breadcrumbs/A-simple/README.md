# Hyvä UI - breadcrumbs.A - simple

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä Breadcrumbs into something new with this UI Component, that adds a new look and feel.

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

| Option Name      | Type    | Available Values   | Default  | Description                                              |
| ---------------- | ------- | ------------------ | -------- | -------------------------------------------------------- |
| `show_back_btn`  | boolean | true, false        | true     | Show a back button on mobile sizes                       |
| `overflow_style` | string  | `normal`, `sticky` | `normal` | Overflow style, when the breadcrumbs don't fit on screen |

## Preview

| Type            | Desktop      | Mobile       |
| --------------- | ------------ | ------------ |
| Default         | ![preview-1] | ![preview-2] |
| Overflow normal |              | ![preview-3] |
| Overflow sticky |              | ![preview-4] |

[preview-1]: ./media/A-simple.jpg "Preview of the Breadcrumbs on Desktop view"
[preview-2]: ./media/A-simple-mobile-back-btn.jpg "Preview of the Breadcrumbs on Mobile view using back button"
[preview-3]: ./media/A-simple-mobile-overflow.jpg "Preview of the Breadcrumbs on Mobile view using overflow"
[preview-4]: ./media/A-simple-mobile-overflow-sticky.jpg "Preview of the Breadcrumbs on Mobile view using overflow style sticky"

> Both overflow options are functional on desktop, while the back button is exclusively for mobile screens.

## Notes

Overflow style `sticky` does not work well with long breadcrumb titles, so make sure your content is not too long or consider using the normal overflow style. 

---

Includes [Schema Data](https://schema.org/) (json-ld), same as seen with the client-side breadcrumbs.

You can disable this by passing the xml argument `skip_ld_json_schema` with the value `true`.

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
