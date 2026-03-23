# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

[Unreleased]: https://gitlab.hyva.io/hyva-themes/magento2-theme-module/-/compare/1.1.2...main

### Added
- Nothing yet.

### Changed
- Nothing.

### Removed
- No removals.

## [1.1.2] - 2022-02-21

[1.1.2]: https://gitlab.hyva.io/hyva-themes/magento2-compat-module-fallback/-/compare/1.1.1...1.1.2

### Added
- Add compat modules to the app/etc/hyva-themes.json configuration file.

  This requires compat modules to be registered with \Hyva\CompatModuleFallback\Model\CompatModuleRegistry in
  the etc/frontend/di.xml file.

### Changed
- Nothing.

### Removed
- No removals.


## [1.1.1] - 2022-02-21

[1.1.1]: https://gitlab.hyva.io/hyva-themes/magento2-compat-module-fallback/-/compare/1.1.0...1.1.1

### Added
- Added methods getCompatModules and getOrigModules

  This will allow generating a console command to dump a list to all compat modules, which in turn then can be used
  to automatically add them to the purge list for tailwind css.

### Changed
- Nothing.

### Removed
- No removals.
