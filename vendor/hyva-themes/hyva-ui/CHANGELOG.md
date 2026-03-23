# Changelog - Hyvä UI

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

[Unreleased]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.6.1...main

## [2.6.1] - 2025-08-29

[2.6.1]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.6.0...2.6.1

### Added

- Added a new plugin to provide experimental support for Tailwind v4.  
  For usage instructions, please refer to the plugin's documentation
  and the new docs for our [NPM package](https://docs.hyva.io/hyva-themes/working-with-tailwindcss/using-hyva-modules/index.html).
- Added a new plugin to provide an example for using design tokens in Tailwind v3

### Changed

- **Product Card A**: Refactored the image template into two separate files for default and CSP Theme

### Fixed

- **Gallery B**: Corrected the initial state display when the starting image is not the first in the gallery
- **Footer B**: Resolved an issue with the toggle state on mobile devices
- **Menu D**: Adjusted the minimum height for first-level menu items
- CSP violations in **Menu C/D**, **Cart A/B**, **Message A/B**, and **Pagination A**

## [2.6.0] - 2025-04-24

[2.6.0]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.5.0...2.6.0

### Added
- **Slider C** Product
- New **Plugin** Snap Slider, for building CSS-driven sliders
- New **Plugin** Html Dialog, for building Modals and Offcanvas elements using the native HTML `<dialog>` element

### Changed
- Updated all UI code to be CSP compliant
- Moved Plugin code to its own new `plugins` directory and updated the documentation to reflect this new location

## [2.5.0] - 2025-01-10

[2.5.0]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.4.1...2.5.0

### Added
- **Breadcrumbs A**
- **Button A**
- **Mobile Menu A/B**
- **Pagination A**
- **Search Form A** with SmileElasticsuite support
- Config options to display icons in **Header A/B/C**
- Wishlist icon to **Header A/B/C**
- Support for static blocks in **Menu C/D** same as **Menu B**
- XML menu support to **Menu A**, same as seen with the Mobile Menus

### Changed
- Removed any inline button styles with the btn variant classes from **Button A**
- Rebuilt logic for Search Form position switch, between mobile and desktop, by using just CSS and without any Javascript

### Fixed
- **Ajax ATC** from opening the Minicart/Ajax Modal after Clearing Cart without a refresh,
  many thanks to Vikram Kumar for their contribution!
- Error handeling in **Ajax ATC** for redirects
- Fixed file uploads with the **Ajax ATC**
- Closing tag in **Sticky ATC A**

### Removed
- Search Form from **Header A/B/C** to its own UI component

## [2.4.1] - 2024-09-10

[2.4.1]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.4.0...2.4.1

### Added
- **Sticky ATC (Add To Cart) A**
- **Scroll To Top B**
- **Sticky Header A**
- `checkHeaderSize` helper to **Header A/B/C** to improve sticky support options

### Changed
- Mobile menu to use the modern scroll lock in**Header A/B/C** to support sticky support

### Fixed
- Invalid qty value not showing the error message in **Minicart A/B**
- Searchbox Autocomplete from effecting the layout in **Header C**
- Searchbox from losing focus when opening the virtual keyboard on mobile devices in **Header C**
- Video interaction in **Gallery B**
- Undefined method for PHPstan in **Ajax ATC A**,
  many thanks to Tjitse Efdé (Vendic) for their contribution!
- Untranslated SVG's in **Popup A/B** and **Product-Data B**,
  many thanks to Lars de Weert (Made by Mouses) for their contribution!

## [2.4.0] - 2024-07-03

[2.4.0]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.3.0...2.4.0

### Added
- **Order Confirmation A**
- **Error Pages A/B**
- i18n boilerplate for UI translations
- badges in the README for UI Components with CMS functionalities for a clearer overview

### Changed
- Updated `x-collapse` for **Accordion.A** and **Product-Data.B**

### Fixed
- **Gallery B** magnifier toggle button still being visible on mobile
- Missing `itemprop=image` in **Gallery B/C/D**

## [2.3.0] - 2024-04-26

[2.3.0]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.2.1...2.3.0

### Added
- **Gallery B/C** magnifier support
- **Scroll To Top A**
- **Menu D** a variation on **Menu C** with the Shop Menu toggle button from **Header B**

### Changed
- **Banner A/B/C/D** use grid stacking to make the banners easier to use in flex, grid and position layouts
- **Gallery C** option for nav renamed from `number` to `counter` to add consistency between gallery options
- Add config options to the **Menu C** for the nesting dept and CTA top link option
- **Menu B** the CMS block position has been rebuild with the Design differences update and now only includes one position with a different name

### Fixed
- **Ajax ATC A** loader being used on buttons that are not the submit button
- **Gallery B** show thumbs when the image count is one
- **Gallery C** fullscreen close button contrast when on the dialog content
- Design differences for **Header A/B/C** and **Menu A/B/C**
- A11Y color issues for **Header C**

### Removed
- Shop Menu toggle button from **Header B**, to allow multiple menus to work with **Header B**

## [2.2.1] - 2024-03-29

[2.2.1]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.2.0...2.2.1

### Added
- Option to configure the form selectors for the **Ajax ATC A**
- Option to load the first image eager for the **Slider A/C**
- PayPal Express In Context support added to the **MiniCart A/B**

### Changed
- **Ajax ATC A** now works with infinite scroll pages that includes forms,
  many thanks to Christoph Hendreich (In Session) for their contribution!

### Fixed
- No active tab in **Product-Data B**, if the description is empty
- **Ajax ATC A** show cart drawer/modal, if there is an error after submitting the form
- **Ajax ATC A** Modal now closes when opening the authentication popup
- **MiniCart A and B** toggle event if `event.detail` is empty
- Review schema type for author in **Product-Review A/B**,
  many thanks to Ravinder (Redchamps) for their contribution!

## [2.2.0] - 2024-02-23

[2.2.0]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.1.0...2.2.0

### Added
- **Accordion A**: added custom icon support
- **Ajax ATC A**
- Carousel to Usps in **Footer C** for mobile view
- **Gallery A/B/D** video preview image support when autoplay is off
- **Loader A**
- **Loader B**
- **MiniCart A and B**
- **Slider C** with SplideJS

### Changed
- **Banner D**: use php foreach loop to make modifications easier
- **Categories A/B**: now use the CSS Slider layout for mobile with an option to keep the grid layout
- **Categories A/B/C**: use php foreach loop to make modifications easier
- **Categories B**: use opacity in svg color for easier color modification
- **Footer B** now uses customer menu with the same xml logic as used for the header
- **Footer B** split the layout back to each Magento 2 module (same as default theme), to make it easier to customize
- **Footer B** The mobile only collapse has been added as its own block same as with the accordion collapse
- **Footer B** use the foreach of each column again from the default theme so other items can still be added
- **Footer C** now extend on **Footer B**
- **Gallery A/B/C/D** Add config support for enabling/disabling the autoplay for the videos
- Optimize svg sizes for **Categories B**, **Slider B** and **Footer A/C**

### Fixed
- **Category-filter A/B** CLS issues
- **Gallery C** (Mobile view) now stops the video from playing if not in view
- **Menu B**: deprecated method for CMS blocks
- **Header A/B/C**: the mobile version now shows the customer, search and language options
- **Header A/B/C**: overflow issues
- A11Y color issues for **Notification A/B** and **Popup A/B**
- Design differences for **Slider A**, **Footer A/B/C**, **Notification A/B**, **Popup A/B**
- Missing php translation option for **Banner A/B/C/D**, **Categories A/B** and **Slider B**

### Removed
- **Categories C** has been rebuild as **Slider C**
- Swatches from usage step in readme for **Category-filter B** and **Product-Card A**

## [2.1.0] - 2023-12-01

[2.1.0]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.0.2...2.1.0

### Added
- **Accordion A**
- **Category-filter A**
- **Category-filter B** for Smile ElasticSuite
- **Gallery A**
- **Gallery B**
- **Gallery C**
- **Gallery D** with SplideJS
- **Product-Data A**
- **Product-Data B**
- **Product-Data C**
- **Product-Review A**
- **Product-Review B**
- **Swatches A**

### Changed
- **Header C**: Fix incorrect padding size
- **Header C**: Remove redundant layout tag in default.xml
- **Assets**: Renamed assets to prevent UTF-8 issues with ä in hyvä
- **Product-Card A**: removed Swatches from component, now replaced by the new **Swatches A** Component

### Removed
- Nothing Removed

## [2.0.2] - 2023-11-15

[2.0.2]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.0.1...2.0.2

### Added
- Nothing added

### Changed
- **General**: Update components to Hyva Theme version 1.3.3
- **General**: Replace incorrect boolean value with string value for aria-hidden attributes on icons
- **Header A/B/C**: Update customer-menu.phtml files to 1.3.2 version of default theme
- **Header A**: Fix z-index issues for the header notification
- **Header B/C**: Remove redundant hidden classes
- **Header B**: Move compare icon to the left to prevent empty spaces
- **Header C**: Fix missing comma

### Removed
- Nothing removed

## [2.0.1] - 2023-10-09

[2.0.1]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.0.1...2.0.0

### Added
- **General**: Add CHANGELOG.md

### Changed
- **General**: Update components to Hyva Theme version 1.3.2
- **General**: Fix wrong link in the main README.md
- **General**: Update License information
- **General**: Fix Varnish caching of menu blocks
- **Menu B**: Remove legacy topmenu file
- **Menu C**: Fix height of 3rd navigation level in menu C
- **Product-card A**: Move script tag inside product container
- **Product-card A**: Fix image scaling
- **Product-card A**: Add focus styling to swatches
- **Slider A/B**: Improve accessibility of slider components

### Removed
- **Menu B**: Removed legacy topmenu file for Hyvä Theme V1.x 

## [2.0.0] - 2023-07-16

[2.0.0]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/compare/2.0.0...1.0.0

### Added
- Nothing added

### Changed
- All components are updated to make them compatible with the latest version of the 
  Hyvä Theme (1.2.x), Tailwind CSS 3 and Alpine.js 3

### Removed
- Nothing removed

## [1.0.0] - 2023-07-16

[1.0.0]: https://gitlab.hyva.io/hyva-themes/ui/hyva-ui/-/tags/1.0.0

### Added
- Initial release added

### Changed
- Nothing changed

### Removed
- Nothing removed
