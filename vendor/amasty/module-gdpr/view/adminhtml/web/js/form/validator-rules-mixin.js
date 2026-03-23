define([
    'jquery'
], function ($) {
    'use strict';

    return function (validator) {
        var requiredInputRule = validator.getRule('required-entry');

        validator.addRule(
            'required-entry',
            function (value) {
                if (value === null) {
                    return true;
                }

                return requiredInputRule.handler(value);
            },
            $.mage.__(requiredInputRule.message)
        );

        return validator;
    }
});
