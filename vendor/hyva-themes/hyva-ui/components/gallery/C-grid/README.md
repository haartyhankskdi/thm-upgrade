# Hyvä UI - gallery.C - Grid

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Tailwind Supported Versions]](https://tailwindcss.com/)
[![AlpineJS Supported Versions]](https://alpinejs.dev/)
[![Figma]](https://www.figma.com/@hyva)

Transform the Hyvä Product Gallery into something new with this UI Component,
that adds a lot of customization options,
so this UI Component can match your preferred look and feel with only a few teaks.

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Catalog/templates/product/view/gallery.phtml`
2. Adjust the content and code to fit your own needs and save
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your
   theme's tailwind directory

### Configuration Options

This UI component offers customization options without modifying the corresponding phtml files.

To configure this UI Component,
utilize the provided options below, with the default value set:

```xml
<var name="gallery">
    <var name="loop">true</var> <!-- Gallery navigation loop (true/false) -->
    <var name="caption">false</var> <!-- Display alt text as image title (true/false) -->
    <var name="allowfullscreen">false</var> <!-- Turn on/off fullscreen (true/false) -->
    <!-- `start` & `end` are Hyva Only options -->
    <var name="arrows">true</var> <!-- Turn on/off arrows on the sides preview (start/end/true/false) -->
    <!-- Hyva Only options -->
    <var name="nav">counter</var> <!-- Gallery navigation style (false/counter) -->
    <var name="fullscreenicon">false</var> <!-- Turn on/off icon for allowfullscreen (true/false) -->
    <var name="maximages">0</var> <!-- Limit the amount of images to show, use 0 to show all -->
    <var name="columns">1</var> <!-- Use a diffrent column count for the grid on larger screens -->
    <var name="magnifier">
        <var name="enable">true</var> <!-- Turn on/off magnifier (true/false) -->
        <var name="zoom">80</var> <!-- magnifier zoom level (integer)-->
    </var>
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

| Type     | Desktop      | Mobile       |
| -------- | ------------ | ------------ |
| Default  | ![preview-2] | ![preview-1] |
| 2columns | ![preview-3] | ![preview-1] |

[Preview-1]: ./media/C-grid-mobile.jpg "Preview of Gallery on Mobile as slider"
[Preview-2]: ./media/C-grid.jpg "Preview of Gallery on Desktop 1column (default)"
[Preview-3]: ./media/C-grid-2columns.jpg "Preview of Gallery on Desktop 2columns style"

## Notes

This UI Component is related to the gallery found in the Hyvä default theme as of version 1.3,
and it also works in versions equal and newer than 1.2.6 if you update the wrapping div with the following classes:

```html
class="order-1 w-full md:w-5/12 lg:w-1/2 md:h-auto"
```

instead of:

```html
class="w-full pt-6 md:pt-0 md:h-auto md:row-start-1 md:row-span-2 md:col-start-1"
```

---

The gallery uses Tailwind CSS classes to switch from list to slider view when the screen size requires a more compact layout. You can adjust the media query to your liking using Tailwind CSS.

---

Unlike the other gallery versions, this version does not use image thumbnails. Instead, it uses a counter to indicate the current slide. This makes more sense for the mobile view, where space is limited.
To enable this, use `<var name="nav">counter</var>` as mentioned above.

---

This gallery is at its best when used with images without borders, as seen in the screenshots.

To apply this option, use the following code within the `Magento_Catalog` options in your `etc/view.xml`:

```xml
<var name="product_image_white_borders">0</var>
```

To add this option, ensure that you only edit the section in your `etc/view.xml` under `Magento_Catalog`:

```xml
<view xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/view.xsd">
    <vars module="Magento_Catalog">
        <!-- Add the options here -->
    </vars>
</view>
```

---

To add a sticky effect to the product information and title on the product detail page,
you'll need to update the `Magento_Catalog/templates/product/product-detail-page.phtml` file in your Hyvä theme.

```html
<section class="text-gray-700 body-font">
    <div class="flex pb-6 md:py-6 lg:flex-row flex-col items-center">
        <div class="grid grid-cols-1 md:gap-x-5 md:grid-cols-[42%_minmax(0,_1fr)] lg:gap-x-10 lg:grid-cols-2 w-full">
            <?= $block->getChildHtml('product.media') ?>
            <div class="md:sticky md:top-0">
                <?= $block->getChildHtml('product.title') ?>
                <?= $block->getChildHtml('product.info') ?>
            </div>
        </div>
    </div>
</section>
```

This will ensure that the product information and title remain visible as you scroll through the product gallery images.

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
