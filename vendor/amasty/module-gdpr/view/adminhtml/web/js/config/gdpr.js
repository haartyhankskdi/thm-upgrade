define([
    "jquery",
    "Magento_Ui/js/modal/modal"
], function ($) {
    'use strict';

    $.widget('mage.amGdprComment', {
        options: {
            comment: ''
        },

        _create: function () {
            this.element.find('.value').append('<p class="note"><span>' + this.options.comment + '</span></p>');
            this.element.on('change', function () {
                this.toggleComment();
            }.bind(this));

            this.toggleComment();
        },

        toggleComment: function () {
            var value = this.element.find('.select').first().val();
            if (parseInt(value)) {
                this.element.find('.note').show();
            } else {
                this.element.find('.note').hide();
            }
        }
    });

    return $.mage.amGdprComment;
});
