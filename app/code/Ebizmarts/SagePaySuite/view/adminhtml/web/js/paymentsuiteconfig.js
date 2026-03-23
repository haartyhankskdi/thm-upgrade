define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'Magento_Ui/js/modal/confirm'
    ],
    function ($, alert, confirm) {
        "use strict";

        $.widget('mage.paymentsuiteconfig', {
            options: {
                configButtonId: '',
                configStateUrl: '',
                eceSelector: 'fieldset[id*=brippo_payments_brippo_payments_ece]',
                brippoRecoverCheckoutSelector: 'fieldset[id*=brippo_payments_recover_checkout]',
                brippoTerminalMOTOSelector: 'fieldset[id*=brippo_payments_terminal_moto_backend]',
                registerLicenseSelector: 'fieldset[id*=sagepaysuite_global]',
            },
            _init(){
                let self = this;

                $('button#' + this.options.configButtonId + '-head').click(() => {
                    this.toggleConfig();
                });

                /* DEBUG MODE */
                $(document).on('change', 'select[id*="ebizmartspaymentsuitegral_payment_suite_debug"]', function() {
                    $('select[id*="brippo_payments_advanced_debug_mode"]').val($(this).val()).change();
                    $('select[id*="sagepaysuite_advanced_debug_mode"]').val($(this).val()).change();
                });
                // todo set default value based on both values

                /* LICENSE */
                $(document).on('click', 'button[id*="payment_suite_register_licence_key"]', function() {
                    self.gotoConfig('opayo_basic');
                });
                /* WALLETS */
                $(document).on('change', 'select[id*="ebizmartspaymentsuitewallets_active"]', function() {
                    self.actionEnable(this, 'ece');
                });

                $(document).on('click', 'button[id*="payment_suite_configure_wallets"]', function() {
                    self.gotoConfig('ece');
                });

                $(document).ready(function () {
                    self.addWalletsLogos();
                });

                /* PAY BY LINK */
                $(document).on('change', 'select[id*="ebizmartspaymentsuitepaybylink_active"]', function() {
                    self.actionEnable(this, 'pay_by_link');
                });
                $('input[id*="ebizmartspaymentsuitepaybylink_title"]').on('keyup', function () {
                    $('input[id*="brippo_payments_brippo_payments_paybylink_title"]').val($(this).val());
                });

                /* RECOVER CHECKOUT */
                $(document).on('change', 'select[id*="ebizmartspaymentsuiterecovercheckout_active"]', function() {
                    self.actionEnable(this, 'recover_checkout');
                });
                $(document).on('click', 'a[id*="payment_suite_recover_checkout"]', function() {
                    self.gotoConfig('recover_checkout');
                });

                /* TERMINAL MOTO */
                $(document).on('change', 'select[id*="ebizmartspaymentsuiteterminalmoto_active"]', function() {
                    self.actionEnable(this, 'terminal_moto');
                });
                $(document).on('click', 'a[id*="payment_suite_terminal_moto"]', function() {
                    self.gotoConfig('terminal_moto');
                });
            },

            findElementIdBySelector(selector){
                const element = $(selector);
                if (element.length && element.attr('id')) {
                    return element.attr('id');
                } else {
                    console.error('paymentsuitejs: Element not found or does not have an ID');
                    return null;
                }
            },

            toggleConfig() {
                const id = this.options.configButtonId;
                const url = this.options.configStateUrl;
                Fieldset.toggleCollapse(id, url);
            },

            isBrippoConfigOpened() {
                const section = $('.section-config.brippo-section.with-button');
                if (section.length && section.hasClass('active')) {
                    return true;
                } else {
                    return false;
                }
            },

            isOpayoConfigOpened() {
                const section = $('.section-config.sagepay-section.with-button');
                if (section.length && section.hasClass('active')) {
                    return true;
                } else {
                    return false;
                }
            },

            openBrippoConfig() {
                if (!this.isBrippoConfigOpened()) {
                    $('button[id*="brippo_payments-head"]').trigger('click');
                }
            },

            openOpayoConfig() {
                if (!this.isOpayoConfigOpened()) {
                    $('button[id*="sagepaysuite-head"]').trigger('click');
                }
            },

            openSetting(setting, settings) {
                const section = $('a[id*=' + settings.head + ']');

                if (section.length && !section.hasClass('open')) {
                    Fieldset.toggleCollapse(
                        this.findElementIdBySelector(settings.selector),
                        this.options.configStateUrl
                    );
                }
            },

            gotoConfig(config) {
                const settings = {
                    "ece" : {"head":"brippo_payments_brippo_payments_ece-head", "selector": "fieldset[id*=brippo_payments_brippo_payments_ece]"},
                    "recover_checkout" : {"head":"brippo_payments_recover_checkout-head", "selector": "fieldset[id*=brippo_payments_recover_checkout]"},
                    "terminal_moto" : {"head":"brippo_payments_terminal_moto_backend-head", "selector": "fieldset[id*=brippo_payments_terminal_moto_backend]"},
                    "opayo_basic" : {"head":"sagepaysuite_global-head", "selector": "fieldset[id*=sagepaysuite_global]"}
                }
                if (!config.includes("opayo")) {
                    if (BrippoAdmin.config.isServiceReady) {
                        this.openBrippoConfig();

                        setTimeout(() => {
                            this.openSetting(config, settings[config]);
                        }, 250);

                        setTimeout(() => {
                            this.scrollToSelector(settings[config].selector, -150);
                        }, 500);
                    } else {
                        this.showBrippoSetupPopup(config);
                    }
                } else {
                    this.openOpayoConfig();

                    setTimeout(() => {
                        this.openSetting(config, settings[config]);
                    }, 250);

                    setTimeout(() => {
                        this.scrollToSelector(settings[config].selector, -150);
                    }, 500);
                }
            },

            actionEnable(el, setting) {
                const settings = {
                    "ece" : {"select":"brippo_payments_brippo_payments_ece_active", "selector": "Wallets Express Checkout"},
                    "pay_by_link" : {"select":"brippo_payments_brippo_payments_paybylink_active", "name": "Pay by Link"},
                    "recover_checkout" : {"select":"brippo_payments_recover_checkout_active", "name": "Recover Checkout"},
                    "terminal_moto" : {"select":"brippo_payments_terminal_moto_backend_active", "name": "Terminal MOTO"},
                    "opayo_basic" : {"select":"sagepaysuite_global-head"}
                }
                if (!setting.includes("opayo")) {
                    if (BrippoAdmin.config.isServiceReady) {
                        $('select[id*="' + settings[setting].select + '"]').val($(el).val()).change();
                    } else {
                        if ($(el).val() === '1') {
                            BrippoAdmin.onboardingRequired.showWithText(
                                'ebizmarts Payment Suite 10',
                                'Brippo\'s <i>'+ settings[setting].name +'</i> requires you to set up a connected account. The process is <b>completely free</b>.'
                            );
                            $(el).val('0').change(); // keep "No" selected
                        }

                    }
                } else {
                    if ($(el).val() === '1') {
                        this.showBrippoSetupPopup('pay_by_link');
                        $(el).val('0').change(); // keep "No" selected
                    }
                }
            },

            showBrippoSetupPopup(reason) {
                switch (reason) {
                    case 'wallets':
                        BrippoAdmin.onboardingRequired.showWithText(
                            'ebizmarts Payment Suite 10',
                            'Brippo\'s <i>Wallets Express Checkout</i> requires you to set up a connected account. The process is <b>completely free</b>.'
                        );
                        break;
                    case 'pay_by_link':
                        BrippoAdmin.onboardingRequired.showWithText(
                            'ebizmarts Payment Suite 10',
                            'Brippo\'s <i>Pay by Link</i> requires you to set up a connected account. The process is <b>completely free</b>.'
                        );
                        break;
                    case 'recover_checkout':
                        BrippoAdmin.onboardingRequired.showWithText(
                            'ebizmarts Payment Suite 10',
                            'Brippo\'s <i>Recover Checkout</i> requires you to set up a connected account. The process is <b>completely free</b>.'
                        );
                        break;
                    case 'terminal_moto_backend':
                        BrippoAdmin.onboardingRequired.showWithText(
                            'ebizmarts Payment Suite 10',
                            'Brippo\'s <i>Terminal MOTO</i> requires you to set up a connected account. The process is <b>completely free</b>.'
                        );
                        break;

                    default:
                        break;
                }
            },

            scrollToSelector(selector, offset = 0) {
                const elId = this.findElementIdBySelector(selector);
                const targetElement = $("#" + elId);

                if (targetElement.length) {
                    $('html, body').animate({
                        scrollTop: targetElement.offset().top + offset
                    }, 1000);
                } else {
                    console.error('paymentsuitejs: Element not found');
                }
            },

            addWalletsLogos() {
                let container = $('a[id*="ebizmartspaymentsuitewallets-head"]');
                let html = '<div class="paymentsuite-wallets-logos-container">' +
                    '<div class="paymentsuite-wallets-logos">' +
                    '<img src="https://brippo.s3.amazonaws.com/images/logo_google_pay.png" width="auto" height="36px">' +
                    '<img src="https://brippo.s3.amazonaws.com/images/logo_apple_pay.png" width="50px">' +
                    '<img src="https://brippo.s3.amazonaws.com/images/logo_link.png" width="50px">' +
                    '<img src="https://brippo.s3.amazonaws.com/images/logo_clear_pay.png" width="auto" height="34px">' +
                    '<img src="https://brippo.s3.amazonaws.com/images/logo_klarna.png" width="auto" height="34px">' +
                '</div></div>';
                container.append(html);
            }
        });

        return $.mage.paymentsuiteconfig;
    }
);
