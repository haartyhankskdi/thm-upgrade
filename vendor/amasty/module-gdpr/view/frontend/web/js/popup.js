define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    function getPopupData(textUrl, consentId)
    {
        return $.ajax({
            async: false,
            url: textUrl,
            cache: true,
            data: { consent_id: consentId }
        });
    }

    return function (config, element) {
        config.buttons = [
            {
                text: $t('I have read and accept'),
                'class': 'action action-primary',
                click: function () {
                    var checkbox = $($('#amgdpr-privacy-popup').data('amgdpr-checkbox-selector'));
                    checkbox.prop('checked', true);
                    checkbox.trigger('change');
                    this.closeModal();
                }
            }
        ];
        config.focus = '.action-primary';

        $(document).on('click', '[data-role="amasty-gdpr-consent"] a[href="#"]',function (e) {
            var targetCheckbox = $(this).closest('div[data-role="amasty-gdpr-consent"]').find('input[type="checkbox"]');
            e.preventDefault();
            e.stopPropagation();
            getPopupData(config.textUrl, targetCheckbox.data('consent-id')).done(function (response) {
                config.title = response.title;
                var popup = modal(config, element);
                popup.element.html(response.content);
                popup.openModal().on('modalclosed', function () {
                    popup.element.html('');
                });
                $('#amgdpr-privacy-popup').closest('.modal-popup').css('z-index', 100001);
                $('#amgdpr-privacy-popup').data('amgdpr-checkbox-selector', '#' + targetCheckbox.attr('id'));
                $('.modals-overlay').css('z-index', 100000);
            });
        });
    };
});
