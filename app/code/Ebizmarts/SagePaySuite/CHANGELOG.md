# Changelog

### Releases
## [10.4.12] - 2025-11-24

__Changed__

- Pi workflow was modified for Hyvä compatibility module to work correctly
  
## [10.4.11] - 2025-07-29

__Fixed__

- Mistake in version numbering being used in README.md

## [10.4.10] - 2025-05-28

__Added__

- SHA-256 support for admin and reporting api. https://wiki.ebizmarts.com/ebizmarts-payments-opayo/changes-to-opayo-xml-api

## [10.4.9] - 2025-04-29

__Changed__

- composer file

## [10.4.8] - 2025-04-08

__Changed__

- Load challenge in iframe to avoid CSP block
- Send orderId in params in FORM controller for easier loading

__Fixed__

- Configuration guide URL
  
## [10.4.7] - 2025-02-25

__Fixed__

- TxAuthNo not being saved on Pi
  
## [10.4.6] - 2025-01-28

__Added__

- More challenge providers to CSP whitelist
- More cases to order cancel cron
- setting to change the lifetime of an order in pending payment

__Changed__

- User-friendly error message for 3DS fails
## [10.4.5] - 2024-09-23

__Added__

- Setting to block orders made with Magento API "Quote Validator"

__Fixed__

- Multiple callbacks on Pi cancelling orders
- License activation not required on each patch update

## [10.4.4] - 2024-08-26

__Added__

- Added setting to hold orders if fraud risk high and autoinvoice is enabled
- Added more challenge issuers to the CSP list for 3D Secure challenge.

__Changed__

- Cronjob checks for successful transactions before cancelling Pending Payment orders

## [10.4.3] - 2024-06-05

__Added__

- Special characters to regex validation
- warning when Pi credentials are invalid
- Opayo Test Domain to CSP whitelist

__Fixed__

- Pi MOTO button and message for 2.4.7
- PayPal can't checkout Virtual Products with Force XML Basket enabled
  
## [10.4.2] - 2024-03-21

__Added__

- Added challenge issuers to the CSP list for 3D Secure challenge.

__Fixed__

- License not activating on checkout if default scope was not activated
- Error message not showing on first try using Pi
- Payment methods using settings from another scope.

## [10.4.1] - 2024-01-23

**Fixed**
- Fixed a bug in the phone validation for UK phone numbers

## [10.4.0] - 2024-01-19

__Added__

- URL Update Compatibility: Ready for the new Elavon and Opayo URL changes.

__Fixed__

- PI integration, page keeps loading at checkout

__Removed__

- Ebizmarts Pay


## [1.4.58] - 2023-10-10
**With support for 2.4.4-p6, 2.4.5-p5 and 2.4.6-p3**

__Added__

- Option to modify cookies for Firefox users using SERVER.

__Fixed__

- FORM error not displaying on checkout page.
- Wiki hyperlink on invalid Opayo API credentials message.

__Changed__
- Installation steps on README.
- Warning message to Pi MOTO.

## [1.4.57] - 2023-07-24

__Added__

- Option to set score for orders to auto invoice.

__Fixed__

- Problem with PI MOTO on multi-store Magento.
- Security Key updates after creating invoice paypal authenticate.

__Changed__
- Detail to 3DSecure fail message.
- Send a generic postcode if the field is left empty in PI.

## [1.4.56] - 2023-06-12

__Added__

- Compatibility between Opayo Suite and Hyvä checkout for Opayo Suite.

## [1.4.55] - 2023-05-31

__Added__

- IPv6 compatibility.

__Fixed__

- Prevent Personal Data Logging doesn't work in Request.log.
- Transaction is voided on first partial refund.
- Gift Message not saving when using a MOTO payment method.
- Prevent the Submit Order button from firing the event twice with PI MOTO.
- Orders not automatically Cancelled when Repeat fails in MOTO.
- Passing null value in postcode field.

__Changed__

- Text changes on configuration.

## [1.4.54] - 2023-04-24

__Added__

- Add Brippo into composer require.
- Magento admin notifications.
- Add cronjob to sync from Opayo API.

__Fixed__

- Error when try to cancel Pi order.
- Special characters issue with apostrophes.
- Fraud grid.
- When cancel transaction using server two quotes are being created.
- Error when phone is selected as optional attribute.

__Changed__

- Match drop in and no drop in labels.
- Transfer SERVER tokens for PI integration.
- Void payment with credit memo when order is created in the same day with PI integration.
- Add extra params to getUrl.

## [1.4.53] - 2023-3-15

__Changed__

- Change opayo by Elavon on the frontend.

__Fixed__

- Opayo fraud column bug on Sales.
- We should imrpove error message if currency not allowed on opayo account.
- Can't create credit note when create invoice after partial release in opayo dashboard.

## [1.4.52] - 2023-2-20

__Fixed__

- Unable to capture opayo transaction - due to an issue in the latest curl library (v7.88.0).

## [1.4.51] - 2023-2-13

__Added__

- Compatibility with Magento 2.4.6 and PHP 8.1.

__Fixed__

- Recover cart with coupons.
- Failed payment emails not sending using pi.
- Repeat charge after partial release.
- SERVER deferred with Paypal fails incorrectly after creating an invoice with lack of funds.
- Invoicing a Repeat Order with Defer causes error.

__Removed__

- Remove 3Dv1 Completely.

## [1.4.50] - 2023-1-11

__Fixed__

- Error when call admin controllers and Magento 2FA is enabled.

## [1.4.49] - 2023-1-9

__Fixed__

- Icons not showing on Fraud Information in order info.
- PI redirecting to a 404 screen instead of success when using 3D.
- On the callback of Pi 3D secure challenge we detect and error and doesn't capture it.
- Order not being automatically cancelled when it fails on MOTO.
- PI MOTO does not close the form after payment fails.
- Validate characters fields before place the order.

__Changed__

- Improve error message for backend orders.
- Repeat's VPSTxId field isn't cleared after insterting an invalid one.

__Removed__

- Removed failed payment emails from main repository.

## [1.4.48] - 2022-11-28

__Fixed__

- Security issue when redirect to callbacks.

## [1.4.47] - 2022-10-20

__Fixed__

- fixed getprefix
- update protocol on core config data
- Orders pending payment when payment is successful

__Added__

- Added missing translations and test to circle
- Create success message for backend orders

## [1.4.46] - 2022-08-29

__Fixed__

- Check if a transaction was successful when cancel an order.

## [1.4.45] - 2022-08-17

__Added__

- Add new logs to log when an order paid with opayo change the status.
- Put a warning in configuration page to recommend not to use PI without Drop In.
- Add ebizmarts Payments copy to configuration.

__Fixed__

- The Value DeliveryAddress 1 is too long when using an Integration other than PI Integration.
- Exception when cancelling order with SERVER (Magento 2.4.4 and php 8.1).
- Orders doesn't get created but Transactions go through Opayo.
- PI without DropIn blocks the Place Order button when swapping to a different Payment Method and swapping back to PI.
- FORM does not load in some checkouts.
- Update transaction status before cancel order.

## [1.4.44] - 2022-07-5

__Added__

- Log AcsUrl.
- Compatibility with Magento captcha.
- Add the possibility of Removing expired Tokens.
- We need add COFUsage to the payment integrations to allow repeat transactions.
- Server configuration option for payment layout.
- Setting to enable or disable repeat transactions.

__Changed__

- Send request against vps protocol 4.00 instead use protcol 3.00 and remove protocol 3.00 setting.

__Fixed__

- Error 'Invalid Length: strongCustomerAuthentication.notificationURL' when using PI MOTO and 3Dv2.
- Error 'Something went wrong: Invalid length: billingAddress.address1' in Checkout when using PI Integration and 3Dv2.
- Missing column sagepaysuite_fraud_check on sales_payment_transaction table.
- Avoid passing null to strpos with Magento 2.4.4 and php 8.1.
- Invalid length: strongCustomerAuthentication.browserLanguage when using Oxford Spelling.
- Prevent Magento 2 Exception error.
- Refused to load the image on admin configuration settings.

## [1.4.43] - 2022-05-03

### Added
- Compatibility with Magento 2.4.4 and php 8.1

### Fixed
- Magento SOAP API issue
- When 3DSecure fails for guests their shipping and billing information is not recovered.

## [1.4.42] - 2022-04-05
### Added
- Use declarative schema approach in module's etc/db\_schema.xml file
- Repeat sending 3Dv2 fields
### Fixed
- Register licence button doesn't work when changes default country
- PI Integration without DropIn locks the Continue to Paypal button for Paypal Integration
- CC Number field has no limit PI form
- Add a character limit in Credit Card Number and Card Verification Number inputs in PI when dropIn disable

## [1.4.41] - 2022-02-07
### Added
- PI integration multi-shipping checkout compatibility.
- License registration setting.
### Fixed
- Wrong transaction id when trying to cancel partial invoiced order.
- Recover cart not working when payment fails/cancelled.
- Module not calling checkout_submit_all_after.
- PI Tokens not working with OSC and FireFox.

## [1.4.40] - 2021-07-07
### Fixed
- Module not recovering cart when PI 3D fails.
- Fraud check failing after Opayo update
- The "ResultInterface" class doesn't exist and the namespace must be specified.

## [1.4.39] - 2021-05-26
### Added
- Compatibility with Magento 2.4.2-p1
- Debug Mode setting
- Prevent customer personal data from logging setting
- Show 3rdMan score and score breakdown on order details
### Changed
- Ask customers if want to save the credit card when they already have tokens
### Fixed
- 0.01 difference when you try to invoice PI Defer orders
- DropIn form not appearing after deleting all tokens on checkout
- The service interface name "Ebizmarts\SagePaySuite\Model\Token\Get" is invalid.
- Invoice created successfully in Magento when transaction was aborted
- PI Authorize and Capture orders not being invoiced
- Recover cart message appearing in product page after successful order with PI and 3D

## [1.4.5.1] - 2021-05-12
### Fixed
- PI with 3D redirecting to cart after checkout

## [1.4.5] - 2021-02-02
### Fixed
- Composer installation problem when requiring Magento vault

## [1.4.4] - 2021-02-01
### Changed
- Added token with vault usage on PI.

### Fixed
- PI repeat with 3Dv2
- Recover cart when session is lost
- Fraud not being retrieved for non default sotres in multi-store setup
- Verification result not showing
- Browser Ipv6 error on PI

## [1.4.3] - 2020-11-24
### Fixed
- 3Dv1 not working with Protocol 4.00 for PI
- PI refund problem with Multi-Store sites
- Duplicated Callbacks received for FORM

## [1.4.2] - 2020-10-27
### Fixed
- Fix duplicate 3D callback and duplicate response for threeDSubmit
- Typo in RecoverCarts.php

## [1.4.1] - 2020-10-06
### Changed
- Server cancel payment redirection to checkout shipping method form

### Fixed
- Added new Order Details fields names in block
- CSP Whitelisting file
- Restriction file added
- PayPal response decrypt issue with PHP7.4
- PayPal POST data fix
- Array key exists fix for PHP7.4
- Fixed unnecesary function calls in restoreCart and Tests
- Quote totals lost on cancel 1200

## [1.4.0] - 2020-08-03
### Changed
- Sage Pay text and logo changed to Opayo

### Fixed
- Adapt 3Dv2 to latest updates
- Duplicated address problem
- 3D, Address, Postcode and CV2 flags not showing up on the order grid
- Recover Cart problem when multiple items with same configurable parent
- Order cancelled when same increment id on different store views
- Duplicated PI Callbacks received cancel the order
- Server not recovering cart when cancel the transaction
- Add form validation in PI WITHOUT Form

[10.4.12]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.12
[10.4.11]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.11
[10.4.9]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.9
[10.4.8]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.8
[10.4.7]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.7
[10.4.6]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.6
[10.4.5]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.5
[10.4.4]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.4
[10.4.3]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.3
[10.4.2]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.2
[10.4.1]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.1
[10.4.0]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/10.4.0
[1.4.56]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.56
[1.4.55]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.55
[1.4.54]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.54
[1.4.53]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.53
[1.4.52]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.52
[1.4.51]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.51
[1.4.50]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.50
[1.4.49]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.49
[1.4.48]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.48
[1.4.47]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.47
[1.4.46]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.46
[1.4.45]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.45
[1.4.44]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.44
[1.4.43]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.43
[1.4.42]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.42
[1.4.41]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.41
[1.4.40]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.40
[1.4.39]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.39
[1.4.5.1]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.5.1
[1.4.5]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.5
[1.4.4]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.4
[1.4.3]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.3
[1.4.2]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.2
[1.4.1]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.1
[1.4.0]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.4.0
