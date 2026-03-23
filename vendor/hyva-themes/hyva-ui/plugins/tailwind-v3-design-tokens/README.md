# Hyvä UI Plugin – Tailwind v3 & Design Tokens

[![License]](../../../LICENSE.md)
[![Tailwind Supported Versions]](https://tailwindcss.com/docs/installation)
[![Figma]](https://www.figma.com/@hyva)

This plugin demonstrates how to integrate Design Tokens into a Hyvä theme.

## Usage – Template

1. Copy or merge the following files/folders into your theme:
   * `web/tailwind/hyva.config.js`
   * `web/tailwind/tailwind.config.js`
2. Ensure you are using **Node.js v20 or higher**.
3. Run `npm install @hyva-themes/hyva-modules@latest` in your theme's `web/tailwind` directory to install the required package.
4. Add the following scripts to the `package.json`
    ```json
    {
        "scripts": {
            "generate": "npx hyva-tokens",
            "prewatch": "npm run generate",
            "prebuild": "npm run generate",
        }
    }
    ```
5. Import the `generated/hyva-tokens.css` in to your `tailwind-source.css`
6. Customize the content and code to fit your project's needs.
7. Build your development or production bundle by running `npm run watch` or `npm run build-prod`.

## Usage – Plugin

This plugin add support for using design design tokens with Hyvä.

- [What are design tokens?](https://docs.hyva.io/hyva-themes/faqs/design-tokens.html)
- [How to use design tokens with Hyvä](https://docs.hyva.io/hyva-themes/working-with-tailwindcss/using-hyva-modules/tokens.html)

## License

Hyvä Themes – https://hyva.io

Copyright © Hyvä Themes B.V 2020–present. All rights reserved.

This product is licensed per Magento installation. Please see the LICENSE.md file in the root of this repository for more information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
