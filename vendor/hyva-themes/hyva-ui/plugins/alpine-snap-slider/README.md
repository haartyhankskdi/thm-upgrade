# Hyvä UI Plugin - Snap Slider

[![License]](../../../LICENSE.md)
[![Hyva Supported Versions]](https://docs.hyva.io/hyva-ui-library/getting-started.html)
[![Figma]](https://www.figma.com/@hyva)

The Hyvä Snap Slider is an AlpineJS plugin that empowers you to create accessible and highly customizable CSS-driven sliders.

It enhances your existing CSS slider implementations by adding essential interactive features.

## Usage - Template

1. Copy or merge the following files/folders into your theme:
   * `Magento_Theme/templates/page/js`
   * `Magento_Theme/layout/default.xml`
2. Adjust the content and code to fit your own needs and save
3. Create your development or production bundle by running `npm run watch` or `npm run build-prod` in your theme's tailwind directory

## Usage - Plugin

To initialize a `snap-slider`, your HTML structure needs a container element with the `x-data` directive
and a direct child element with the `[data-track]` attribute.

```html
<section x-data x-snap-slider>
    <div data-track>
        <!-- Slides here go here -->
    </div>
</section>
```

The interactive behavior of the slider is then managed by the CSS styles you apply to the elements within the `[data-track]` container.

**Important:** While the plugin provides the JavaScript logic, the visual presentation of the slider is entirely dependent on your CSS styling.

This component prioritizes accessibility by automatically managing the `inert` attribute for off-screen slides and adding appropriate ARIA labels, ensuring a better experience for all users.

You can further enhance the slider with navigation buttons (next/previous) and pagination markers by adding elements with specific `data-` attributes (explained in the "Configuration Options" section).

**Core Principle:** The Hyvä Snap Slider follows a progressive enhancement approach. It relies on CSS for the fundamental styling and uses JavaScript to add interactivity and accessibility features.

### Modifiers

The `x-snap-slider` directive accepts modifiers to customize its default behavior.

#### `.auto-pager`

The `.auto-pager` modifier automatically generates a set of pagination markers (dots) after the `[data-track]` element.

To control the placement of the auto-generated pager, include an empty `[data-pager]` container within the `snap-slider` element. The pager will be inserted into this container.

```html
<section x-data x-snap-slider.auto-pager>
    <div data-track>
        <!-- Slides here go here -->
    </div>
    <div data-pager></div>
</section>
```

Consider setting a `min-height` on the `data-pager` to prevent layout shifts as the pager is added.

You can style the auto-generated pager using the default CSS classes `.pager` and `.marker`. Alternatively, for integration with CSS utility libraries or custom styling, you can apply your own classes using the `data-pager-classes` and `data-marker-classes` attributes on the `x-snap-slider` element.

#### `.group-pager`

The `.group-pager` modifier automatically groups the pagination dots if more slides are visible.

### Configuration Options

The `snap-slider` plugin utilizes the following `data-` attributes for configuration:

| Data Attribute                | Description                                                                                                      |
| :---------------------------- | :--------------------------------------------------------------------------------------------------------------- |
| `data-track`                  | Identifies the HTML element that contains the individual slides of the slider.                                   |
| `data-next`                   | Designates the HTML element (e.g., a button) that triggers navigation to the next slide.                         |
| `data-prev`                   | Designates the HTML element (e.g., a button) that triggers navigation to the previous slide.                     |
| `data-pager`                  | Specifies the HTML element that will serve as the container for custom pagination markers.                       |
| `data-slide-label-sepparator` | Allows you to customize the separator used when generating accessible labels for slides (e.g., "Slide 1 of \*"). |

> All configuration options are implemented using HTML `data-` attributes (e.g., `[data-track]`).

#### Custom Pager Implementation

When implementing a custom pager, each slide within the `[data-track]` element must have a unique `id` attribute. These IDs are then used to link the pager markers to their corresponding slides. This can be achieved using either:

  * **Anchor links (`<a>` tags):** Set the `href` attribute of the link to the slide's ID prefixed with a hash (`#`).
  * **Buttons:** Use `<button>` elements with the `[data-target-id]` attribute set to the corresponding slide's ID.

Styling the appearance of your custom pager markers is entirely up to your CSS implementation.

## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2020-present. All rights reserved.

This product is licensed per Magento install. Please see the LICENSE.md file in the root of this repository for more
information.

[License]: https://img.shields.io/badge/License-004d32?style=for-the-badge "Link to Hyvä License"
[Figma]: https://img.shields.io/badge/Figma-gray?style=for-the-badge&logo=Figma "Link to Figma"
[Fylgja AlpineJS Dialog]: https://fylgja.dev/components/alpine-dialog/

[Hyva Supported Versions]: https://img.shields.io/badge/Hyv%C3%A4-1.3.11-0A23B9?style=for-the-badge&labelColor=0A144B "Hyvä Supported Versions"
