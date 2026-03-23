define([
    'jquery'
], function ($) {
    'use strict';

    return function () {
        var checkBox = $('[data-gdpr-js="agree"]'),
            blockSelector = '[data-gdpr-js="content"]',
            fieldsetSelector = '[data-gdpr-js="fieldset"]';

        checkBox.click(function () {
            var currentCheckBox = $(this),
                block = currentCheckBox.closest(blockSelector);

            if (this.checked) {
                block.find(fieldsetSelector).removeAttr('hidden');
                block.find('div.mage-error').remove();
            } else {
                block.find(fieldsetSelector).attr('hidden', true);
            }
        });
    }
});
