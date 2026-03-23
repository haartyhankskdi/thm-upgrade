# Hyvä Themes - Compatibility Module View Fallback

[![Hyvä Themes](https://hyva.io/media/wysiwyg/logo-compact.png)](https://hyva.io/)

## hyva-themes/magento2-compat-module-fallback

## Installation

This module is usually installed automatically by composer as a dependency of Hyvä Themes compatibility modules.
If you nevertheless want to install it manually (for example if you would like to contribute), run 

```
composer config repositories.hyva-themes/magento2-compat-module-fallback git git@gitlab.hyva.io:hyva-themes/magento2-compat-module-fallback.git
composer require hyva-themes/magento2-compat-module-fallback
```

## Purpose

This module allows registering Hyvä compatibility modules for automatic view file overrides.
The purpose of this is to avoid boilerplate layout XML and di.xml plugins, when block content is added in a result
programmatically by a module.

Once a module is registered as a Hyvä compatibility module, templates of the original module can be overridden by
placing the `.phtml` file in the same location in the compatibility module.
For example:

Template location in the original module: `Orig_Module::page/example.phtml`
Template location in the compatibility module: `Hyva_OrigModule::page/example.phtml`

In addition to the automatic tempalte overrides, modules registered with the compat module registry will also be
added to the app/etc/hyva-themes.json file, and can hook into the tailwind styles.css build process.

## Usage

Compatibility modules are registered with the `CompatModuleRegistry` using `frontend/di.xml`.

Since modules can have more than one compatibility module, and compatibility modules can
have more than one original module, the constructor argument map can not be a simple associative array.

The argument array structure looks like this:

```
<type name="Hyva\CompatModuleFallback\Model\CompatModuleRegistry">
    <arguments>
        <argument name="compatModules" xsi:type="array">
            <item name="hyva_origmodule_map" xsi:type="array">
                <item name="original_module" xsi:type="string">Orig_Module</item>
                <item name="compat_module" xsi:type="string">Hyva_OrigModule</item>
            </item>
            <item name="hyva_othermodule_map" xsi:type="array">
                <item name="original_module" xsi:type="string">Other_Module</item>
                <item name="compat_module" xsi:type="string">Hyva_OtherModule</item>
            </item>
        </argument>
    </arguments>
</type>
```

The above example registers the module `Hyva_OrigModule` as a compatibility module for both `Orig_Module` and `Other_Module`.

Note: The keys of the first level of the array do not matter, as long as they are unique.

### Details

The first example in the Purpose section above glossed over some details. The fallback actually contains two additional
folders for each compatibility module. 

This is useful when:
- The compatibility module handles more than one original module.
- Both original modules have a template file with the same name. 

The following lists of fallback modules hopefully make it clear: 

#### Original fallback

1. `<theme_dir>/<module_name>/templates`
2. `<module_dir>/view/<area>/templates`
3. `<module_dir>/view/base/templates`

#### Fallback with one compatibility module for Orig_Module

1. `<theme_dir>/<module_name>/templates`
2. `<compat_module_dir>/view/<area>/templates/Orig_Module`
3. `<compat_module_dir>/view/<area>/templates`
4. `<module_dir>/view/<area>/templates`
5. `<compat_module_dir>/view/base/templates/Orig_Module`
5. `<compat_module_dir>/view/base/templates`
5. `<module_dir>/view/base/templates`

#### Example:

* Original files:
  - `Orig_Module::cookie_bar.phtml`
  - `Other_Module::cookie_bar.phtml`
* Overrides:
  - `Hyva_OrigModule::Orig_Module/cookie_bar.phtml`
  - `Hyva_OrigModule::Other_Module/cookie_bar.phtml`


## License

Hyvä Themes - https://hyva.io

Copyright © Hyvä Themes B.V 2021-present. All rights reserved.

Please see [License File](LICENSE.md) for more information.

## Changelog

Please see [The Changelog](CHANGELOG.md).
