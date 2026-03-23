# Hyvä UI - swatches.A - Rounded Swatches

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

This component replaces the standard product option swatches in Layered Navigation, Product Listings and Product Detail Page with a new look and feel.

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Swatches/templates`
   * `Hyva_SmileElasticsuite/templates/swatch/product/layered/renderer.phtml`
2. Adjust the content and code to fit your own needs and save
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component has no configurable options.

If you are using image swatches,
you might however want to configure the `swatch_image` value in your theme's `etc/view.xml` file for better display.

To configure Swatch image sizes,
insert or edit the provided option below, with your preferred image dimensions:

```xml
<view xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/view.xsd">
   ...
   <media>
      <images module="Magento_Catalog">
         <image id="swatch_image" type="swatch_image">
            <width>30</width>
            <height>30</height>
         </image>
         <image id="swatch_image_base" type="swatch_image">
            <width>30</width>
            <height>30</height>
         </image>
      </images>
   </media>
   ...
</view>
```

## Preview

| Type                | Desktop      |
| ------------------- | ------------ |
| Layered navigation  | ![preview-1] |
| Product Detail Page | ![preview-2] |
| Product Listing     | ![preview-3] |

[preview-1]: ./media/A-rounded-swatches-layered-nav.jpg "preview of swatch in layered navigation"
[preview-2]: ./media/A-rounded-swatches-product-detail.jpg "preview of swatch in product detail page"
[preview-3]: ./media/A-rounded-swatches-product-listing.jpg "preview of swatch in grid view"

## Notes

The `x-defer` tag is only supported with Hyva theme module version 1.3.7 or higher.

If you're using an older version of the Hyva theme module, this tag will be ignored.

For about the [`x-defer` Apline plugin](https://docs.hyva.io/hyva-themes/view-utilities/alpine-defer-plugin.html) see our docs.

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
