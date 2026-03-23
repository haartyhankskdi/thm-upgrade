# Brippo Payments
Magento Brippo Payments integrations.

### Installation via Composer

   ```
   composer config repositories.ebizmarts composer https://packages.ebizmarts.com
   composer config http-basic.brippo.gitlab.ebizmarts.com brippo VBZL3yALPqrWHcf7UAB7
   composer require ebizmarts/brippo-payments
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento setup:static-content:deploy
   ```

### Manual Installation

   1. Download latest brippo-payments package.
   2. Create app/code/Ebizmarts/BrippoPayments folder and unpack the content of the package into it.
   3. [Optional] If legacy app/code/Ebizmarts/BrippoPaymentsFrontend folder is present, remove it.
   4. Run the following commans:
   ```
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento setup:static-content:deploy
   ```
   
