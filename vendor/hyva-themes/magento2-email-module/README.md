# hyva-themes/magento2-email-module

Luma style Emails for Hyvä Themes because currently Hyvä does not style emails.

## What does it do?

This module reactivates the luma CSS functionality for emails in hyva-based themes.
The `email.less` file should go in your active Hyvä theme, into the directory `web/css/`.

Note this is different from the luma-checkout or the theme-fallback modules, that actually use a Luma theme instead of Hyvä. 
 
## Installation
  
1. Install via composer
   Note: both repositories need to be configured until the package and its dependency are available through packagist.
   ```
   composer config repositories.hyva-themes/magento2-email-module git git@gitlab.hyva.io:hyva-themes/magento2-email-module
   composer require hyva-themes/magento2-email-module
   ```
2. Enable module
   ```
   bin/magento setup:upgrade
   ```

