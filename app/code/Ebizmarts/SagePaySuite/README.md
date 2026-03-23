# Sage Pay Suite integration for Magento 2

[CHANGELOG](https://github.com/ebizmarts/magento2-sage-pay-suite/blob/master/CHANGELOG.md)

[WIKI](https://wiki.ebizmarts.com/opayo)

## Installation Instructions

## Composer installation (preferred method)
1. Run these commands on Magento's root dir. Contact us to get your access token.

`composer config repositories.ebizmarts composer https://packages.ebizmarts.com`

`composer config http-basic.gitlab.ebizmarts.com token your_token`

`composer config http-basic.brippo.gitlab.ebizmarts.com token your_token`

`composer config http-basic.sagepay.gitlab.ebizmarts.com token your_token`

`composer require ebizmarts/sagepaysuite`

Replace "your_token" with the token provided on your subscription.

Note: If is the first module you install via composer probably magento will also ask for Magento authentication keys. You can read how obtain your keys here:
https://devdocs.magento.com/guides/v2.4/install-gde/prereq/connect-auth.html

If you are asked for username and password during the composer require procedure, use "token" as username and your_token as password.

2. Install the module.

`bin/magento setup:upgrade`

`bin/magento setup:di:compile`

`bin/magento static:content:deploy`

## Token expired and it's causing problems.

If your token expired and it's causing problems when you run composer update. 
You can renew your support to get a new token or simply run `composer config --unset repositories.ebizmarts`


## Manual installation

__Installation Magento version 2.4.x__
 
  1. Upload the ZIP file to the Magento 2 server, we higly recommend create a TEMPORARY folder for this (out of `$MAGENTO_FOLDER$`).

  2. Get access to the Magento 2 server.

  3. Go to the TEMPORARY folder and uncompress Sage Pay Suite packages on the TEMPORARY folder created on the first step (the 'xx' at the end of the package stands for the last two digits belonging to the version number being used).

    $ unzip /PATH/TEMPORARY/TO/PACKAGES/Ebizmarts_OpayoSuiteM2-10.4.xx.zip

  4. Go to the Magento2 modules folder.

    $ cd $MAGENTO_FOLDER$/app/code

  5. Create the directory (if it does not exist) that will hold the module contents
    `$ mkdir Ebizmarts`
   
  6. Go to the magento root folder (where composer.json is located)
  
    $ cd $MAGENTO_FOLDER$
   
  7. Here you will need choose the version depending on your Magento version:
        - If you are running Magento 2.4.4 you will need copy the content from the folder 2.4.4 executing the following command:
          `$ cp -a /PATH/TEMPORARY/TO/PACKAGES/Ebizmarts_OpayoSuiteM2-1.4.43/2.4.4/app/code/Ebizmarts/* $MAGENTO_FOLDER$/app/code/Ebizmarts/`
        - If you are running Magento 2.4.3 or less you will need copy the content from the folder 2.4.3 executing the following command:
          `$ cp -a /PATH/TEMPORARY/TO/PACKAGES/Ebizmarts_OpayoSuiteM2-1.4.43/2.4.3/app/code/Ebizmarts/* $MAGENTO_FOLDER$/app/code/Ebizmarts/`
  7. This will create the following content in `$MAGENTO_FOLDER$`/app/code
    <pre>
    └── Ebizmarts
        └── SagePaySuite
        └── SagePaySuiteFormCrypt
        └── SagePaySuiteLogger
    </pre>
  8. Go to the magento root folder (where composer.json is located)

    $ cd $MAGENTO_FOLDER$

  9. Execute Magento setup upgrade

    $ bin/magento setup:upgrade

  10. Clean cache and generated code

    $ bin/magento cache:clean
    
    $ rm -rf var/generation/*

  11. Run magento compiler to generate auto-generated classes

    $ bin/magento setup:di:compile

   (this will take some time ...)
   





## Contributing (for form-crypt and logger modules)

We recommend using [modman](https://github.com/colinmollenhour/modman) in your development environment, to isolate our code from the rest of the Magento instance. A tutorial is available [here](https://github.com/colinmollenhour/modman/wiki/Tutorial). 

After installing **modman**, you can run `modman init` at the root of your Magento instance, which will create a `.modman` directory. You should:
1. `cd .modman` from the Magento root.
2. Clone this repository, as well as the repositories for all the required modules. Make sure to select the correct branch using option **-b** 
3. Move back to the Magento root, and run `modman deploy-all`.
4. Install the modules with `bin/magento setup:upgrade` && `bin/magento setup:di:compile`.

**Note: If you're using version 2.4.4 or later, you must use the ***develop-101*** branch. If you're using a version up to 2.4.3, you must use the ***develop-100*** branch**


### Required modules:

- [ebizmarts/magento2-sage-pay-suite](https://github.com/ebizmarts/magento2-sage-pay-suite) `modman clone git@github.com:ebizmarts/magento2-sage-pay-suite.git`
- [ebizmarts/magento2-sage-pay-suite-formcrypt](https://github.com/ebizmarts/magento2-sage-pay-suite-formcrypt) `modman clone git@github.com:ebizmarts/magento2-sage-pay-suite-formcrypt.git`
- [ebizmarts/magento2-sage-pay-suite-logger](https://github.com/ebizmarts/magento2-sage-pay-suite-logger) `modman clone git@github.com:ebizmarts/magento2-sage-pay-suite-logger.git`
- [ebizmarts/magento2-sage-pay-suite-test](https://github.com/ebizmarts/magento2-sage-pay-suite-test) `modman clone git@github.com:ebizmarts/magento2-sage-pay-suite-test.git`

__Test__

  You can check if the module was properly installed testing some features introduced by Sage Pay Suite:
  
  1. Get access to the Magento 2 backoffice.

  2. Menu > Stores > Configuration > SALES > Payment Methods
  You should see Sage Pay Suite on the payment methods list.
  3. Enter your Sage Pay vendorname and Ebizmarts license key on the configuration settings.
  4. Enable the integration of your preference.

[![Build Status](https://circleci.com/gh/ebizmarts/magento2-sage-pay-suite.svg?style=shield&circle-token=9d950c73b76af8868862caf8400c549439838d47)](https://circleci.com/gh/ebizmarts/magento2-sage-pay-suite)


