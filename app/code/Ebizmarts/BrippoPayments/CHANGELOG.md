# Changelog

This file documents all changes made in the Brippo Payments extension project.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Releases

### [101.0.21] - Nov 27, 2025

__Added__

- Automatic processing of uncaptured payment orders in NEW state.
- Failsafe and notification for orders going straight to complete instead of processing.

### [101.0.20] - Oct 29, 2025

__Fixed__

- Frontend serialization issues.
- City errors with restricted characters.
- Admin get config issues.

### [101.0.19] - Sep 30, 2025

__Fixed__

- Klarna serialization issues on occasional instances.
- Fix cancelled quantities when recovering an order.
- Retry frontend confirmation when "object in use" error.
- Invalid website id when website and store have uncorrelated ids.

### [101.0.18] - Ago 26, 2025

__Added__

- added setting to specify which logos are displayed for PayByLink

__Fixed__

- PayByLink error "enter a valid payment method and try again"

### [101.0.17] - Ago 20, 2025

__Added__

- Support for latest Hyvä Themes and Hyvä Checkout.

### [101.0.16] - Ago 7, 2025

__Added__

- Wallet Express Checkout payment list mode.
- Take delivery estimate from cart page for Wallet Express Checkout.
- Admin tool for manually setting order as processing.

__Fixed__

- Configuration endpoint 404 error when store domain includes folder.

### [101.0.15] - Jul 30, 2025

__Added__

- Receipt for Backend Terminal orders.
- Billing data ported to Brippo Portal for Backend Terminal orders.

__Fixed__

- PHP 7.3 support issues.

### [101.0.14] - Jul 22, 2025

__Added__

- Standalone Checkout, with Pay by Bank, Klarna and Billie support.

### [101.0.13] - Jul 7, 2025

__Fixed__

- API key being overridden by **** if config re-saved.

__Added__

- Improved general order flow and payment logs/analytics.
- Recover checkout signature to avoid exposing customer addresses. 

### [101.0.12] - Jun 13, 2025

__Fixed__

- Form key error on product page loaded the first time for Wallets Express Checkout.
- PHP 8.4 compilation warnings.

__Added__

- Wallets Express Checkout regenerate button if removed.
- Recover Checkout general improvements.
- Pickup input values feature.

### [101.0.11] - May 20, 2025

__Fixed__

- Fix for Deprecated Error: explode() Passing Null to Parameter #2

### [101.0.10] - May 19, 2025

__Added__

- Soft Fail recovery.

__Changed__

- Improved Recover Checkout analytics and general fixes.

__Fixed__

- GetPaymentMethods error in admin when service not ready.

### [101.0.9] - May 08, 2025

__Added__

- Wallet button not showing on mobile

### [101.0.8] - Apr 30, 2025

__Added__

- PHP 8.4 and Magento 2.4.8 support.

### [101.0.7] - Apr 28, 2025

__Fixed__

- GB postal code failsafe for cutted codes fix.

### [101.0.6] - Apr 22, 2025

__Fixed__

- CSP issues with 3ds popup on some browser versions.
- Multi-currency setting not being picked up.
- Fixes on customer refresh failsafe.

### [101.0.5] - Apr 11, 2025

__Added__

- Checkout form payment methods configuration.

### [101.0.4] - Mar 28, 2025

__Fixed__

- Amasty rewards points compatibility

### [101.0.3] - Mar 25, 2025

__Fixed__

- errors related to missing shipping data

__Changed__

- checkout form initialization (retry)

### [101.0.2] - Mar 19, 2025

__Added__

- MageSide support.
- Block customers by group.

__Fixed__

- Recover checkout not loading.
- Error on specific use case: can't make payment missing data.

### [101.0.1] - Mar 4, 2025

__Added__

- Terminal payment method now supports card present mode as well as MO/TO.

__Changed__

- Merged frontend package.

__Fixed__

- Shipping restrictions issue.

### [100.4.6] - Feb 20, 2025

__Added__

- New Terminal MOTO payment method.

__Fixed__

- Recover orders cron failing on some PHP versions.

### [100.4.5] - Feb 6, 2025

__Fixed__

- Recover checkout automatic notifications disabled by default. Require service to be ready.

### [100.4.4] - Jan 28, 2025

__Fixed__

- 2FA not working for admin.

### [100.4.3] - Jan 24, 2025

__Added__

- Recover checkout.

__Fixed__

- Rounding issue in amount monitor check.

### [100.4.2] - Dec 24, 2024

__Changed__

- Curl library fixes for some magento stores.

### [100.4.1] - Nov 27, 2024

__Changed__

- New Connected Account setup flow.

### [100.3.1] - Nov 14, 2024

__Changed__

- Order status new scheme. Brippo custom statuses introduced.

__Added__

- 3DS result column in Order Grid.

### [100.2.1] - Oct 24, 2024

__Added__

- Authorize-only order status change option.
- Klarna & ClearPay support.

### [100.1.47] - Oct 09, 2024

__Fixed__

- Monitor cancelling Pay by Link orders

### [100.1.46] - Sep 30, 2024

__Fixed__

- Paypal express support exception.

### [100.1.45] - Sep 23, 2024

__Added__

- Paypal integration support.
- Duplicate payments notification fixes to avoid false positives.

### [100.1.44] - Sep 11, 2024

__Changed__

- Register domain improvements.

### [100.1.43] - Sep 2, 2024

__Added__

- Statement descriptor suffix option.
- Send user agent in metadata.
- Link existing account functionality.
- Debug mode can now be set from config.

__Changed__

- Monitor improvements.

### [100.1.42] - Aug 6, 2024

__Changed__

- Improved monitor cron to cancel orders with no/invalid payment id.

### [100.1.41] - Jul 24, 2024

__Added__

- Improved Hyva support.

__Fixed__

- Webhook mapping error.

__Changed__

- Improved fulfillment monitor tool.

### [100.1.40] - Jun 21, 2024

__Fixes__

- Brippo Api connection fixes.

### [100.1.39] - Jun 18, 2024

__Added__

- Keep sensitive data out of logs option.
- Support for magento 2.2.8.
- Payment from applicable countries option.

### [100.1.38] - May 31, 2024

__Fixed__

- Cope with other Brippo Api success status codes causing intermittent issues.

### [100.1.37] - May 23, 2024

__Added__

- Configurable default order status.
- Show more connected account values in config section.

__Fixed__

- Country issue when determining payment fees.

### [100.1.36] - Apr 30, 2024

__Changed__

- Brippo Api endpoint for POS orders processing.

### [100.1.35] - Apr 1, 2024

__Added__

- Payment info in order emails improved.
- Multi-currency support and fixes.

### [100.1.34] - Mar 11, 2024

__Fixed__

- Payment info showing broken wallet info.

### [100.1.33] - Mar 4, 2024

__Changed__

- Uncaptured payments warning message fixes.
- Remove Stripe PHP lib dependency.

__Fixed__

- Capture multi-currency orders with wrong amount.

### [100.1.32] - Feb 13, 2024

__Added__

- Uncaptured payments warning message.
- Save ip address in metadata.

### [100.1.31] - Jan 30, 2024

__Added__

- Www domain registration for store domain.
- Payment details now included in order email.

### [100.1.30] - Nov 21, 2023

__Added__

- Partial capture support.

### [100.1.29] - Nov 6, 2023

__Fixed__

- Internal improvements.

### [100.1.28] - Oct 24, 2023

__Fixed__

- Spinner stuck if ApplePay modal cancelled in product page.
- Unable to clear blocked delivery methods select completely.

__Added__

- Fraud Risk columns in order grid.
- Italian & French translations.

### [100.1.27] - Oct 2, 2023

__Fixed__

- Populate payments table base_amount_authorized.

__Added__

- Payments monitor cron.
- Pay by Link.

### [100.1.26] - Sep 11, 2023

__Fixed__

- Re-add support for legacy linkTransfer flow.

__Added__

- ARN Number for refunds.

### [100.1.25] - Sep 4, 2023

__Changed__

- Linking removed from payment flow.

### [100.1.24] - Aug 24, 2023

__Fixed__

- 3D Secure 2 not cancelling order after fail authentication.

__Added__

- Order Payment information shows failed messages.

### [100.1.23] - Aug 22, 2023

__Added__

- Force 3D Secure options for PE.
- Cancel uncaptured order if authentication expires at Stripe's.
- Expire Stripe auth if uncaptured order is cancelled.
- Webhooks new system.
- Fraud data display.

### [100.1.22] - Aug 4, 2023

__Changed__

- Onboarding service improvements.

### [100.1.21] - Jul 24, 2023

__Fixed__

- Admin script fix to avoid issues in some magento versions.
- V3 for Payments Service to include fee calculation fixes.

### [100.1.20] - Jul 4, 2023

__Fixed__

- True refund id in order comments plus refund ID validation.
- Declarative Schema is not up-to-date error.

### [100.1.19] - Jun 26, 2023

__Added__

- Payments service improvements.

### [100.1.18] - Jun 6, 2023

__Fixed__

- Multi-currency refunds amount.
- Checkout issue when unable to detect browser type.

__Added__

- Cronjob to process pending order in case of network outage.
- Add extension signature in all payments.

### [100.1.17] - Jun 6, 2023

__Fixed__

- Failsafe for customer fingerprint issues.

### [100.1.16] - May 29, 2023

__Fixed__

- Add payment information for deferred payments after capture.

### [100.1.15] - May 26, 2023

__Fixed__

- PHP 8.2 support fixes.

### [100.1.14] - May 23, 2023

__Added__

- New dashboard support.

### [100.1.13] - May 18, 2023

__Added__

- New dashboard support.

### [100.1.12] - May 11, 2023

__Added__

- PHP 8.2 Support.

### [100.1.11] - May 2, 2023

__Changed__

- Customer linking improved performance.

### [100.1.10] - Apr 21, 2023

__Added__

- Custom transaction descriptions for dashboard activity feed.
- Browsers email protection failsafe.
- Customer linking and creation for dashboard information.
- Expanded payment information for magento backend.

### [100.1.8] - Apr 14, 2023

__Fixed__

- Refunds only reversing transfer error.

### [100.1.7] - Apr 13, 2023

__Fixed__

- Order payment info livemode error.
- Livemode error in backend config.

__Added__

- Apple domain registrar support for payment service API.

### [100.1.6] - Apr 11, 2023

__Changed__

- Payments Service API v2 update.
- Logs format.

### [1.1.5] - Mar 22, 2023

__Changed__

- Stripe PHP library requirement.

### [1.1.4] - Mar 9, 2023

__Changed__

- Terms & cond URL.

### [1.1.3] - Mar 1, 2023

__Changed__

- Support for latest Payments Service API.

### [1.1.2] - Feb 23, 2023

__Fixed__

- Order payment info for POS payment methods.
- Webhooks redirection.

### [1.1.1] - Feb 16, 2023

__Added__

- Express integration.

[101.0.21]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.21
[101.0.20]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.20
[101.0.19]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.19
[101.0.18]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.18
[101.0.17]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.17
[101.0.16]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.16
[101.0.15]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.15
[101.0.14]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.14
[101.0.13]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.13
[101.0.12]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.12
[101.0.11]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.11
[101.0.10]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.10
[101.0.9]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.9
[101.0.8]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.8
[101.0.7]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.7
[101.0.6]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.6
[101.0.5]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.5
[101.0.4]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.4
[101.0.3]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.3
[101.0.2]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.2
[101.0.1]: https://github.com/ebizmarts/brippo-payments/releases/tag/101.0.1
[100.4.6]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.4.6
[100.4.5]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.4.5
[100.4.4]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.4.4
[100.4.3]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.4.3
[100.4.2]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.4.2
[100.4.1]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.4.1
[100.3.1]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.3.1
[100.2.1]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.2.1
[100.1.47]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.47
[100.1.46]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.46
[100.1.45]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.45
[100.1.44]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.44
[100.1.43]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.43
[100.1.42]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.42
[100.1.41]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.41
[100.1.40]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.40
[100.1.39]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.39
[100.1.38]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.38
[100.1.37]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.37
[100.1.36]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.36
[100.1.35]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.35
[100.1.34]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.34
[100.1.33]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.33
[100.1.32]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.32
[100.1.31]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.31
[100.1.30]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.30
[100.1.29]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.29
[100.1.28]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.28
[100.1.27]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.27
[100.1.26]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.26
[100.1.25]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.25
[100.1.24]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.24
[100.1.23]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.23
[100.1.22]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.22
[100.1.21]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.21
[100.1.20]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.20
[100.1.19]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.19
[100.1.18]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.18
[100.1.17]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.17
[100.1.16]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.16
[100.1.15]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.15
[100.1.14]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.14
[100.1.13]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.13
[100.1.12]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.12
[100.1.11]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.11
[100.1.10]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.10
[100.1.8]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.8
[100.1.7]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.7
[100.1.6]: https://github.com/ebizmarts/brippo-payments/releases/tag/100.1.6
[1.1.5]: https://github.com/ebizmarts/brippo-payments/releases/tag/1.1.5
[1.1.4]: https://github.com/ebizmarts/brippo-payments/releases/tag/1.1.4
[1.1.3]: https://github.com/ebizmarts/brippo-payments/releases/tag/1.1.3
[1.1.2]: https://github.com/ebizmarts/brippo-payments/releases/tag/1.1.2
[1.1.1]: https://github.com/ebizmarts/brippo-payments/releases/tag/1.1.1
