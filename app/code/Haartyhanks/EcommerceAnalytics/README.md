# Mage2 Module Haartyhanks EcommerceAnalytics

    ``haartyhanks/module-ecommerceanalytics``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Hi my name is nilesh i have created this extension for ecommerce purpose so that website can send values to google for tracking purpose

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Haartyhanks`
 - Enable the module by running `php bin/magento module:enable Haartyhanks_EcommerceAnalytics`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require haartyhanks/module-ecommerceanalytics`
 - enable the module by running `php bin/magento module:enable Haartyhanks_EcommerceAnalytics`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - Block
	- Ecommerce > ecommerce.phtml


## Attributes



