# Hyvä UI - notification.B - full width

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä Message into something new with this UI Component, that adds a new look and feel.

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Theme/templates/messages.phtml`
   * `Magento_Theme/layout/default.xml`
2. Adjust the content and code to fit your own needs and save
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI component,
utilize the provided options as outlined in the `src/Magento_Theme/layout/default.xml` file.

| Option Name    | Type    | Available Values          | Default | Description                                           |
| -------------- | ------- | ------------------------- | ------- | ----------------------------------------------------- |
| `display_mode` | string  | `default`, `blank`, `tag` | `tag`   | Show the message with colors, just text or with a tag |
| `show_icon`    | boolean | true, false               | true    | Determines whether to show the icon                   |

## Preview

| Type    | No icon      | With Icon    |
| ------- | ------------ | ------------ |
| Default | ![preview-1] | ![preview-2] |
| Blank   | ![preview-3] | ![preview-4] |
| Tag     | ![preview-5] | ![preview-6] |

[preview-1]: ./media/B-full-width.jpg "Preview of Notification"
[preview-2]: ./media/B-full-width-icon.jpg "Preview of Notification with icon"
[preview-3]: ./media/B-full-width-blank.jpg "Preview of Blank Notification"
[preview-4]: ./media/B-full-width-blank-icon.jpg "Preview of Blank Notification with icon"
[preview-5]: ./media/B-full-width-tag.jpg "Preview of Tag Notification"
[preview-6]: ./media/B-full-width-tag-icon.jpg "Preview of Tag Notification with icon"

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
