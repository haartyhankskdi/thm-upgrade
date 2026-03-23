/**
 * Manageable Cookie Logic
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    return {
        groups: [],
        force: false,

        isChecked: function (groupId) {
            return this.force || (!!groupId && this.groups.indexOf(groupId) !== -1);
        },

        updateGroups: function (groups) {
            this.setForce(false);
            this.groups = [];
            _.each(this.prepareGroups(groups), function (group) {
                if (group.checked) {
                    this.groups.push(parseInt(group.groupId));
                }
            }.bind(this));
        },

        prepareGroups: function (groups) {
            return groups.groupData || groups;
        },

        setForce: function (force) {
            this.force = force;
        },

        processManageableCookies: function () {
            $('script[data-amcookie-groupid][type="text/plain"]').each(function (i, elem) {
                if (this.isChecked($(elem).data('amcookie-groupid'))) {
                    $(elem).remove().attr('type', 'text/javascript').appendTo('body');
                }
            }.bind(this));
        },
    };
});
