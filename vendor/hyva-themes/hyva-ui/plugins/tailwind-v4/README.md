# Hyvä UI Plugin – Tailwind v4 & Design Tokens

[![License]](../../../LICENSE.md)
[![Tailwind Supported Versions]](https://tailwindcss.com/docs/installation)
[![Figma]](https://www.figma.com/@hyva)

> ⚠️ **Experimental:** This plugin provides an experimental option for using Tailwind CSS v4 with Hyvä.
> While functional, Hyvä is not yet fully compatible with Tailwind v4, so you may encounter some issues.

This plugin demonstrates how to integrate Tailwind v4 into a Hyvä theme.

## Usage – Template

1. Replace your theme’s `web/tailwind` folder with the one from this plugin.
2. Ensure you are using **Node.js v20 or higher**.
3. Customize the content and code to fit your project's needs.
4. Run `npm install` in your theme's `web/tailwind` directory to install the required packages.
5. Build your development or production bundle by running `npm run watch` or `npm run build-prod`.

## Usage – Plugin

### Tailwind v4 Integration

Tailwind v4 introduces several breaking changes.
This UI plugin is a draft solution that may be incorporated into a future version of the Hyvä default theme.
For a complete list of breaking changes,
refer to the [Tailwind v4 Upgrade Guide](https://tailwindcss.com/docs/upgrade-guide).

We have aimed to stay as close as possible to the original Tailwind workflow,
avoiding custom ViteJS or PostCSS solutions seen in other PHP frameworks like Laravel.

Instead, we provide custom Node.js scripts (included in the `@hyva-themes/hyva-modules` package)
that preserve the module merging logic from Tailwind v3.

For more information on the new Node.js scripts,
see our documentation on [Hyvä Modules](https://docs.hyva.io/hyva-themes/working-with-tailwindcss/using-hyva-modules/index.html).

#### Supported but Not Recommended

[Tailwind’s JavaScript configuration is still supported](https://tailwindcss.com/docs/upgrade-guide#using-a-javascript-config-file) via the new CSS `@config` rule.

You can include it with:

```css
@config "./tailwind.config.js";
```

However, this configuration only applies to `@theme` settings; purging and content settings are ignored.

---

`@apply` no longer works for `@layers`.
To register custom CSS utilities, use the `@utility` directive.
This is a primary reason Hyvä is not yet fully compatible with Tailwind v4,
as many modules rely on the older approach.

### Design Tokens

This plugin also includes a sample implementation for using design tokens with Hyvä.

- [What are design tokens?](https://docs.hyva.io/hyva-themes/faqs/design-tokens.html)
- [How to use design tokens with Hyvä](https://docs.hyva.io/hyva-themes/working-with-tailwindcss/using-hyva-modules/tokens.html)

#### Alternatives

If you are not ready for Tailwind v4 but want to use design tokens,
we offer another plugin in Hyvä UI for Tailwind v3.
It includes a similar setup, allowing you to use design tokens with the stable version of Tailwind.

## License

Hyvä Themes – https://hyva.io

Copyright © Hyvä Themes B.V 2020–present. All rights reserved.

This product is licensed per Magento installation. Please see the LICENSE.md file in the root of this repository for more information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"
[Tailwind Supported Versions]: https://img.shields.io/badge/Tailwind-4-06B6D4?style=for-the-badge&logo=TailwindCSS "Tailwind Supported Versions"
