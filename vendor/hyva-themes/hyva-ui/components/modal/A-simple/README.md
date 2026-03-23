# Hyvä UI - modal.A - simple

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

This UI Component provides practical examples of implementing modals in Hyvä themes,
showcasing both the classic Hyvä modal approach and the modern `x-htmldialog` method.

> :warning:
> These examples require adaptation before direct use in your theme.
> Adjust content and code to fit your specific requirements.

## Usage

1. Transfer the desired example file from the `template/examples` directory into your theme's template structure.
2. Modify the content and code within the copied files to align with your project's needs.
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your theme's tailwind directory

## Modal Implementation Approaches

### Classic Hyvä Modals

This approach aligns with the modal implementation outlined in the [Hyvä documentation](https://docs.hyva.io/hyva-themes/view-utilities/modal-dialogs/index.html).

#### ViewModel Integration (PHP)

The `Magento_Theme/templates/examples/classic.phtml` file demonstrates modal creation using a PHP ViewModel.

#### JavaScript-Only Implementation

The `Magento_Theme/templates/examples/classic-js.phtml` file illustrates a JavaScript-centric approach, more suitable for modals within CMS content, if adjusted to this.

### Modern `x-htmldialog` Modals

Leveraging the native HTML `<dialog>` element and the `x-htmldialog` Alpine.js plugin, this method simplifies modal management.

- **Plugin Requirement:** Ensure the `x-htmldialog` plugin is correctly integrated as detailed in the plugin's documentation.
- **Simplified Logic:** This approach eliminates the need for extensive custom JavaScript, resulting in cleaner and more maintainable code.
- **Hyvä Integration:** Styling and additional JavaScript logic seamlessly integrate with Hyvä's conventions.

## Preview

| Desktop      | Mobile       |
| ------------ | ------------ |
| ![preview-1] | ![preview-2] |

[preview-1]: ./media/A-simple.jpg "Desktop Modal Preview"
[preview-2]: ./media/A-simple-mobile.jpg "Mobile Modal Preview"

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
