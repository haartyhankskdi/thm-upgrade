# magento2-theme-fallback

Theme fallback for Hyvä Themes.

## What does it do?

It allows to use the luma theme or define another theme path for specific URLs.
 
## Installation for Hyvä license holders
  
1. Install via composer
```
composer require hyva-themes/magento2-theme-fallback
```
2. Enable module
```
bin/magento setup:upgrade
```

## Installation for contributions or technology partners
  
1. Install via composer
```
composer config repositories.hyva-themes/magento2-theme-fallback git git@gitlab.hyva.io:hyva-themes/magento2-theme-fallback.git
composer require hyva-themes/magento2-theme-fallback:dev-main
```
2. Enable module
```
bin/magento setup:upgrade
```
  
## Magento backend configuration

1. ```HYVA THEMES->Theme Fallback->General Settings->Enable```
    
    The configuration path is ```hyva_theme_fallback/general/enable```


2. ```HYVA THEMES->Theme Fallback->General Settings->Theme full path```

    The configuration path is ```hyva_theme_fallback/general/theme_full_path```
    
    default `frontend/Magento/luma`

3. ```HYVA THEMES->Theme Fallback->General Settings->The list of URL's parts```

   The configuration path is ```hyva_theme_fallback/general/list_part_of_url```

  
## How does it work?

There is a `before-plugin` for all frontend controllers. 
The theme fallback is applied when:
1. Current `route/controller/action` path matches to configured part.  
   * Example: the configured url is `customer/account`.  
     Then for all requests such as ``customer/account/*` the fallback would be applied.
   * Example: the configured url is `customer/account/login`.
     Only for `Login` page the fallback would be applied.
2. The part of current url matches to configured part.
    * Example: the configured url is `demo-product.html`.
      All pages with url contains `demo-product.html` hold have the fallback. 
   
