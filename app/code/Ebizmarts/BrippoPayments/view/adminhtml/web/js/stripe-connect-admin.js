define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'Magento_Ui/js/modal/confirm'
    ],
    function ($, alert, confirm) {
        "use strict";

        $.widget('mage.brippoAdmin', {
            options: {
                scope: "",
                scopeId: "",
                logsUrl: "",
                resetUrl: "",
                configButtonId: "",
                configStateUrl: "",
                onboardingResponseUrl: '',
                onboardingGoToUrl: ''
            },
            _init(){
                $('#brippoBtnSetupLive').click(() => {
                    this.setupConnectedAccount(true);
                });
                $('#brippoBtnSetupTest').click(() => {
                    this.setupConnectedAccount(false);
                });
                $('.brippopayments.version').click(() => {
                    this.downloadLogs();
                });
                $('#brippoBtnResetTest').click(() => {
                    this.reset(false);
                });
                $('#brippoBtnResetLive').click(() => {
                    this.reset(true);
                });
                $('button#' + this.options.configButtonId + '-head').click(() => {
                    this.toggleConfig();
                });

                $('[name="groups[brippo_payments][groups][global][fields][statement_descriptor_suffix][value]"]').on(
                    'keyup',
                    () => { this.updateStatementDescriptorPreview(); }
                );
                this.updateStatementDescriptorPreview();
            },
            setupConnectedAccount(liveMode) {
                window.location.href = this.options.onboardingGoToUrl
                    + (liveMode ? 'live' : 'test')
                    + '/magento/'
                    + encodeURIComponent(this.options.onboardingResponseUrl);
            },
            downloadLogs() {
                window.open(this.options.logsUrl, '_blank');
            },
            reset(livemode) {
                confirm({
                    content: $.mage.__('Are you sure you want to reset this connected account?'),
                    actions: {
                        confirm: () => {
                            $.ajax({
                                url: this.options.resetUrl,
                                data: {
                                    'scope': this.options.scope,
                                    'scopeId': this.options.scopeId,
                                    'liveMode': livemode ? 1 : 0
                                },
                                type: 'GET',
                                dataType: 'json',
                                showLoader: true
                            }).done((data) => {
                                if (data.valid === 0) {
                                    alert({content: 'Error: ' + data.message});
                                } else if (data.valid === 1) {
                                    location.reload();
                                }
                            });
                        }
                    }
                });
            },
            toggleConfig() {
                const id = this.options.configButtonId;
                const url = this.options.configStateUrl;
                Fieldset.toggleCollapse(id, url);
            },
            updateStatementDescriptorPreview() {
                const suffix = $('[name="groups[brippo_payments][groups][global][fields][statement_descriptor_suffix][value]"]').val();
                const liveMode = $('[name="groups[brippo_payments][groups][global][fields][mode][value]"]').val() !== 'test';
                const descriptorLive = $('.brippoStatementDescriptorValueLIVE');
                const descriptorTest = $('.brippoStatementDescriptorValueTEST');
                let finalDescriptor = '';

                if (liveMode && descriptorLive.length > 0) {
                    finalDescriptor = descriptorLive.html() + '* ' + suffix;
                } else if (!liveMode && descriptorTest.length > 0) {
                    finalDescriptor = descriptorTest.html() + '* ' + suffix;
                }

                if (finalDescriptor.length > 22) {
                    $('.brippoStatementDescriptorPreview').html(
                        '<span style="color: red;">Your final statement descriptor can not be more than 22 characters long: '
                        + '<span class="descriptorPreview">' + finalDescriptor + '</span></span>'
                    );
                } else {
                    $('.brippoStatementDescriptorPreview').html(
                        'Your final statement descriptor will be: '
                        + '<span class="descriptorPreview">'
                        + finalDescriptor + '</span>'
                    );
                }
            }
        });
        return $.mage.brippoAdmin;
    }
);
