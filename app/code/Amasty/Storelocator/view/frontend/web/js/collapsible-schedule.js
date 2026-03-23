define([
    'jquery',
    'collapsible'
], function ($) {

    $.widget('amlocator.collapsibleSchedule', $.mage.collapsible, {
        options: {
            ajaxContent: true,
            openedState: 'active',
            animate: 200
        },

        /**
         * @private
         * @returns {void}
         */
        _create: function () {
            // For wcag compatibility with tabpanel role
            this.options.content = $(this.element).find(this.options.content);
            this._super();
        }
    });

    return $.amlocator.collapsibleSchedule;
});
