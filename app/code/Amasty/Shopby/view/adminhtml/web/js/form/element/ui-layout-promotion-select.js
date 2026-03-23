define([
    'Amasty_Base/js/form/element/ui-promotion-select',
    'mage/translate'
],function (UiSelect, $t) {
    'use strict';

    return UiSelect.extend({
        /**
         * @returns {Object}
         */
        initialize: function () {
            this._super();

            if (this.default && !this.value().length) {
                const defaultOption = this.options().find(option => option.value === this.default);

                !!defaultOption && this.value(defaultOption.value);
            }

            let options = this.options();
            const noLayoutUpdatesRecord = options?.find(record => record.value === '');
            if (options && options.length && !noLayoutUpdatesRecord) {
                options.unshift({label: $t('No layout updates'), value: '', __disableTmpl: true, level: 0, path: ''});
            }

            return this;
        },

        /**
         * @returns string
         */
        setCaption: function () {
            let placeholder = this._super();

            if (_.isArray(this.value())) {
                this.value('');
            }

            return placeholder;
        }
    });
});
