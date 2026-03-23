/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    "jquery",
    "prototype"
], function ($) {
    MpSocials = new Class.create();
    MpSocials.prototype = {
        initialize: function () {
        },
        sendAjax: function (url, currentUrl) {
            $.ajax({
                method: 'POST',
                url: url,
                data: {
                    current_url: currentUrl
                }
            });
        }
    };
});
